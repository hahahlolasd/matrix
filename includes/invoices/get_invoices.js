$(function () {
    $.fn.DataTable.ext.pager.numbers_length = 5;
	
    // Get language from cookie
    function getLangCookie() {
        return document.cookie.split('; ').find(row => row.startsWith('lang='))?.split('=')[1] || 'en';
	}
	
    var userLang = getLangCookie();
	
    // Calculate date range for past 2 weeks
    var today = new Date();
    var twoWeeksAgo = new Date();
    twoWeeksAgo.setDate(today.getDate() - 14);
    
    // Format dates as YYYY-MM-DD for database
    function formatDateForDatabase(date) {
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
	}
    
    var defaultDateFrom = formatDateForDatabase(twoWeeksAgo);
    var defaultDateTo = formatDateForDatabase(today);
    
    // Get display format based on language
    function getDisplayFormat(lang) {
        switch(lang) {
            case 'hu': return 'Y. M. d';    // Hungarian: 2025.12.18
            case 'sr': return 'd. M. Y';    // Serbian: 18.12.2025
            default: return 'Y-m-d';      // English/ISO: 2025-12-18
		}
	}
    
    var displayFormat = getDisplayFormat(userLang);
    
    // Initialize "From" datepicker
    $('#invoices_date_from').flatpickr({
        enableTime: false,
        dateFormat: 'Y-m-d',           // Database format
        altInput: true,                // Show alternative input
        altFormat: displayFormat,      // Display format based on language
        defaultDate: defaultDateFrom,  // Default date
        maxDate: 'today',              // Can't select future dates
        onChange: function(selectedDates, dateStr, instance) {
            var toDatepicker = $('#invoices_date_to')[0]._flatpickr;
            
            // If "From" date is after "To" date, update "To" date
            if (toDatepicker && selectedDates[0]) {
                var toDate = toDatepicker.selectedDates[0];
                if (toDate && selectedDates[0] > toDate) {
                    toDatepicker.setDate(selectedDates[0], false);
				}
			}
            
            // Update maxDate of "To" datepicker
            if (toDatepicker) {
                toDatepicker.set('minDate', selectedDates[0] || null);
			}
            
            // Trigger table refresh
            if (typeof table !== 'undefined') {
                table.draw();
			}
		}
	});
    
    // Initialize "To" datepicker
    $('#invoices_date_to').flatpickr({
        enableTime: false,
        dateFormat: 'Y-m-d',           // Database format
        altInput: true,                // Show alternative input
        altFormat: displayFormat,      // Display format based on language
        defaultDate: defaultDateTo,    // Default date
        maxDate: 'today',              // Can't select future dates
        onChange: function(selectedDates, dateStr, instance) {
            var fromDatepicker = $('#invoices_date_from')[0]._flatpickr;
            
            // If "To" date is before "From" date, update "From" date
            if (fromDatepicker && selectedDates[0]) {
                var fromDate = fromDatepicker.selectedDates[0];
                if (fromDate && selectedDates[0] < fromDate) {
                    fromDatepicker.setDate(selectedDates[0], false);
				}
			}
            
            // Update minDate of "From" datepicker
            if (fromDatepicker) {
                fromDatepicker.set('maxDate', selectedDates[0] || 'today');
			}
            
            // Trigger table refresh
            if (typeof table !== 'undefined') {
                table.draw();
			}
		}
	});
	
    window.invoicesTable = $('#invoices_table').DataTable({
        lengthChange: true,
        pageLength: 10,
        scrollX: true,
        autoWidth: false,
        language: {
            searchPlaceholder: translations.search + '...',
            sSearch: '',
            lengthMenu: translations.displayed + ' _MENU_ ' + translations.entries,
            paginate: {
                previous: '<i class="bi bi-chevron-left"></i>',
                next: '<i class="bi bi-chevron-right"></i>'
			}
		},
		columns: [
			{ data: 'id', title: translations.id },
			{ 
				data: 'file', 
				title: translations.file,
				className: 'dt-center',
				render: function(data, type, row) {
					return `
					<div class="btn btn-success download-file" 
					data-filename="${data}" 
					title="${translations.download}">
					<i class="bi bi-download"></i> ${translations.download}
					</div>`;
				}
			},
			{ 
				data: 'date', 
				title: translations.date,
				render: function(data, type, row) {
					if (!data) return '';
					
					var date = new Date(data + 'T00:00:00');
					
					// Create formatter based on language
					var formatter;
					if (userLang === 'hu') {
						formatter = new Intl.DateTimeFormat('hu-HU', {
							year: 'numeric',
							month: 'short',
							day: '2-digit'
						});
						} else if (userLang === 'sr') {
						formatter = new Intl.DateTimeFormat('sr-RS', {
							year: 'numeric',
							month: 'short', 
							day: '2-digit'
						});
						} else {
						formatter = new Intl.DateTimeFormat('en-CA'); // YYYY-MM-DD
					}
					
					return formatter.format(date);
				}
			},
			{ data: 'creator_name', title: translations.creator },
			{
				data: null,
				title: translations.actions,
				className: 'dt-center',
				render: function (data, type, row) {
					return `
					<i class="bi bi-eye dt-icon view-icon" 
					data-id="${row.id}" 
					data-filename="${row.file}" 
					title="${translations.view}"></i>
					<i class="bi bi-trash dt-icon delete-icon" 
					data-id="${row.id}" 
					title="${translations.delete}"></i>
					`;
				}
			}
		],
        processing: true,
        serverSide: true,
        ajax: {
            url: 'includes/invoices/get_invoices.php',
            type: 'GET',
            data: function (d) {
                // Get database format dates from inputs
                var dateFrom = $('#invoices_date_from').val() || defaultDateFrom;
                var dateTo = $('#invoices_date_to').val() || defaultDateTo;
                
                d.date_from = dateFrom;
                d.date_to = dateTo;
                
                return d;
			},
            dataSrc: function (json) {
                return json.data;
			}
		},
        createdRow: function (row, data, dataIndex) {
            $('td', row).last().addClass('action-buttons');
		},
        dom: "<'row'<'col-sm-12'tr>>" +
		"<'row'<'col-sm-12'p>>"
	});
	
	// Handle file download
	$(document).on('click', '.download-file', function() {
		var filename = $(this).data('filename');
		var downloadUrl = 'assets/invoices/' + filename;
		
		// Create a temporary link and trigger download
		var link = document.createElement('a');
		link.href = downloadUrl;
		link.download = filename;
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
	});
	
	// Handle view button click
	$(document).on('click', '.view-icon', function() {
		var invoiceId = $(this).data('id');
		var filename = $(this).data('filename');
		
		// Show loading state
		$('#viewInvoiceModal .modal-body').html(`
			<div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
			<span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading invoice details...</p>
			</div>
		`);
		
		// Fetch invoice details
		$.ajax({
			url: 'includes/invoices/get_invoice_details.php',
			type: 'GET',
			data: { 
				id: invoiceId,
				filename: filename,
				lang: userLang
			},
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					// First, replace the entire modal body with the actual content structure
					$('#viewInvoiceModal .modal-body').html(`
						<!-- Daily Summary Bar -->
						<div class="bg-light p-3 border-bottom">
						<div class="row">
						<div class="col-md-4">
                        <small class="text-muted d-block">Date</small>
                        <strong id="modalDate">-</strong>
						</div>
						<div class="col-md-4">
                        <small class="text-muted d-block">Total Items</small>
                        <strong id="modalItemCount">0</strong>
						</div>
						<div class="col-md-4">
                        <small class="text-muted d-block">Total Amount</small>
                        <strong id="modalDailyTotal">0.00</strong>
						</div>
						</div>
						</div>
						
						<!-- Items Table -->
						<div class="p-3">
						<div class="table-responsive">
						<table class="table table-sm table-hover mb-0" id="dailyItemsTable">
                        <thead>
						<tr class="table-light">
						<th width="5%">#</th>
						<th width="45%">Item Name</th>
						<th width="15%" class="text-end">Quantity</th>
						<th width="15%" class="text-end">Unit Price</th>
						<th width="20%" class="text-end">Total</th>
						</tr>
                        </thead>
                        <tbody id="dailyItemsBody">
						<!-- Items will be populated here -->
                        </tbody>
                        <tfoot class="table-light">
						<tr>
						<td colspan="4" class="text-end fw-bold">Grand Total:</td>
						<td class="text-end fw-bold" id="grandTotal">0.00</td>
						</tr>
                        </tfoot>
						</table>
						</div>
						
						<!-- Empty state -->
						<div id="emptyState" class="text-center py-5 d-none">
						<i class="bi bi-inbox fs-1 text-muted mb-3"></i>
						<p class="text-muted">No items found for this day</p>
						</div>
						</div>
					`);
					
					// Now populate the data
					populateInvoiceModal(response.data);
					} else {
					showError('Failed to load invoice details: ' + (response.message || 'Unknown error'));
				}
			},
			error: function(xhr, status, error) {
				console.error('AJAX Error:', error, xhr.responseText); // Debug log
				showError('Error loading invoice details: ' + error);
			}
		});
		
		// Show the modal
		var modal = new bootstrap.Modal(document.getElementById('viewInvoiceModal'));
		modal.show();
	});
	
	// Function to populate the modal with daily items
	function populateInvoiceModal(dailyData) {
		// Format currency based on language
		function formatCurrency(amount, lang) {
			if (amount === null || amount === undefined || isNaN(amount)) return '0.00';
			
			var formatter = new Intl.NumberFormat(lang === 'sr' ? 'sr-RS' : 
				lang === 'hu' ? 'hu-HU' : 'en-US', {
					minimumFractionDigits: 2,
					maximumFractionDigits: 2
				});
				
				return formatter.format(amount);
		}
		
		// Format number (for quantities)
		function formatNumber(value, lang) {
			if (value === null || value === undefined || isNaN(value)) return '0';
			
			var formatter = new Intl.NumberFormat(lang === 'sr' ? 'sr-RS' : 
				lang === 'hu' ? 'hu-HU' : 'en-US', {
					minimumFractionDigits: 0,
					maximumFractionDigits: 3
				});
				
				return formatter.format(value);
		}
		
		// Format date for display
		function formatDisplayDate(dateString, lang) {
			if (!dateString) return 'Unknown date';
			
			var date = new Date(dateString + 'T00:00:00');
			if (isNaN(date.getTime())) return dateString;
			
			if (lang === 'hu') {
				return date.toLocaleDateString('hu-HU', {
					year: 'numeric',
					month: 'long',
					day: 'numeric'
				});
				} else if (lang === 'sr') {
				return date.toLocaleDateString('sr-RS', {
					year: 'numeric',
					month: 'long',
					day: 'numeric'
				});
				} else {
				return date.toLocaleDateString('en-US', {
					year: 'numeric',
					month: 'long',
					day: 'numeric'
				});
			}
		}
		
		// Update summary information
		var displayDate = formatDisplayDate(dailyData.date, userLang);
		$('#modalDate').text(displayDate);
		$('#modalItemCount').text(dailyData.item_count || 0);
		$('#modalDailyTotal').text(formatCurrency(dailyData.daily_total, userLang));
		
		// Populate items table
		var itemsBody = $('#dailyItemsBody');
		var emptyState = $('#emptyState');
		
		if (dailyData.items && dailyData.items.length > 0) {
			itemsBody.empty();
			emptyState.addClass('d-none');
			
			var grandTotal = 0;
			
			dailyData.items.forEach(function(item, index) {
				var itemTotal = item.TotalAmount || (item.UnitPrice * item.Quantity) || 0;
				grandTotal += itemTotal;
				
				var row = `
                <tr>
				<td class="text-muted">${index + 1}</td>
				<td>
				<div class="item-name" title="${item.Name || ''}">${item.Name || 'Unknown item'}</div>
				</td>
				<td class="text-end">
				<span class="number-cell">${formatNumber(item.Quantity, userLang)}</span>
				</td>
				<td class="text-end">
				<span class="number-cell">${formatCurrency(item.UnitPrice, userLang)}</span>
				</td>
				<td class="text-end fw-semibold">
				<span class="number-cell">${formatCurrency(itemTotal, userLang)}</span>
				</td>
                </tr>
				`;
				itemsBody.append(row);
			});
			
			// Update grand total
			$('#grandTotal').text(formatCurrency(grandTotal, userLang));
			
			} else {
			itemsBody.empty();
			emptyState.removeClass('d-none');
			$('#grandTotal').text('0.00');
		}
		
		// Update modal title
		$('#viewInvoiceModalLabel').html(`<i class="bi bi-list-check me-2"></i>Daily Items: ${displayDate}`);
	}
	
	// Add CSS for better display
	var style = document.createElement('style');
	style.textContent = `
    .item-name {
	max-width: 300px;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
    }
    
    .number-cell {
	font-family: 'SFMono-Regular', 'Consolas', 'Liberation Mono', 'Menlo', monospace;
	font-size: 0.9em;
    }
    
    #dailyItemsTable tbody tr:hover {
	background-color: rgba(0, 0, 0, 0.02);
    }
    
    #dailyItemsTable td, #dailyItemsTable th {
	padding: 0.5rem 0.75rem;
    }
    
    @media (max-width: 768px) {
	.item-name {
	max-width: 200px;
	}
	
	#dailyItemsTable {
	font-size: 0.85rem;
	}
    }
	`;
	document.head.appendChild(style);
	
	// Helper function to extract date from filename
	function extractDateFromFilename(filename) {
		// Extract date from filename like "invoices_2025-12-18.json"
		var match = filename.match(/(\d{4}-\d{2}-\d{2})/);
		if (match) {
			var date = new Date(match[1] + 'T00:00:00');
			if (userLang === 'hu') {
				return date.toLocaleDateString('hu-HU');
				} else if (userLang === 'sr') {
				return date.toLocaleDateString('sr-RS');
				} else {
				return date.toLocaleDateString('en-US');
			}
		}
		return filename;
	}
	
	// Export function (optional)
	function exportDailyItems() {
		// This would export the items table as CSV
		alert('Export functionality would be implemented here');
	}
	
	// Function to show error messages
	function showError(message) {
		$('#viewInvoiceModal .modal-body').html(`
			<div class="alert alert-danger" role="alert">
			<i class="bi bi-exclamation-triangle-fill me-2"></i>
			${message}
			</div>
			<div class="text-center mt-3">
			<button type="button" class="btn btn-primary" onclick="location.reload()">
			<i class="bi bi-arrow-clockwise me-1"></i> Try Again
			</button>
			</div>
		`);
	}
	
});