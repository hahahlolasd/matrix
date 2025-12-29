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
			if (window.invoicesTable) {
				window.invoicesTable.draw();
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
			if (window.invoicesTable) {
				window.invoicesTable.draw();
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
					data-date="${row.date}"
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
	
	// Create and inject the modal HTML dynamically
    function createDailyItemsModal() {
		const modalHTML = `
		<div class="modal fade" id="viewInvoiceModal" tabindex="-1" aria-labelledby="viewInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
		<div class="modal-header">
		<h5 class="modal-title" id="viewInvoiceModalLabel"><i class="bi bi-list-check me-2"></i></h5>
		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="${translations.close}"></button>
		</div>
		<div class="modal-body p-0">
		<div id="modalBodyContent">
		
		<!-- Daily Summary Bar -->
		<div class="bg-light p-3 border-bottom">
		<div class="row">
		
		<div class="col-md-4">
		<small class="text-muted d-block">${translations.date}</small>
		<strong id="modalDate"></strong>
		</div>
		
		<div class="col-md-4">
		<small class="text-muted d-block">${translations.total_price}</small>
		<strong id="modalDailyTotal">0.00</strong>
		</div>
		
		<div class="col-md-4">
		<select id="cashRegisterFilter" class="form-select select2"></select>
		</div>
		
		</div>
		</div>
		
		<!-- Items Table -->
		<div class="px-3 py-1">
		<div class="table-responsive table-striped modal-table">
		<table class="table table-sm table-hover mb-0" id="dailyItemsTable">
		<thead class="modal-thead">
		<tr>
		<th width="5%">#</th>
		<th width="45%">${translations.product_name}</th>
		<th width="15%" class="text-end">${translations.quantity}</th>
		<th width="15%" class="text-end">${translations.unit_price}</th>
		<th width="20%" class="text-end">${translations.total_price}</th>
		</tr>
		</thead>
		<tbody id="dailyItemsBody">
		<!-- Items will be populated here -->
		</tbody>
		<tfoot class="table-light">
		<tr>
		<td colspan="4" class="text-end fw-bold">${translations.grand_total}:</td>
		<td class="text-end fw-bold" id="grandTotal">0.00</td>
		</tr>
		</tfoot>
		</table>
		</div>
		
		<!-- Empty state -->
		<div id="emptyState" class="text-center py-5 d-none">
		<i class="bi bi-inbox fs-1 text-muted mb-3"></i>
		<p class="text-muted">${translations.no_items_found}</p>
		</div>
		
		<!-- Loading state -->
		<div id="loadingState" class="text-center py-5">
		<div class="spinner-border text-primary" role="status">
		<span class="visually-hidden">${translations.loading}</span>
		</div>
		<p class="mt-2">${translations.loading}</p>
		</div>
		</div>
		</div>
		<div class="modal-footer">
		<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${translations.close}</button>
		</div>
		</div>
        </div>
		</div>
		</div>
		
		`;
		
		$('#dailyItemsModalContainer').html(modalHTML);
	}
    
    // Call this function to create the modal
    createDailyItemsModal();
    
    // Global variables to store data
	let currentDailyData = null;
	let currentFilename = null;
	
	// Handle view button click
	$(document).on('click', '.view-icon', function() {
		currentFilename = $(this).data('filename');
		var invoiceDate = $(this).data('date');
		
		// Show loading state
		showLoadingState();
		
		// Show the modal
		var modal = new bootstrap.Modal(document.getElementById('viewInvoiceModal'));
		modal.show();
		
		// Store the date in the modal for later use
		$('#viewInvoiceModal').data('invoice-date', invoiceDate);
		
		// Fetch cash registers
		loadCashRegisters(currentFilename, invoiceDate);
	});
	
	function loadCashRegisters(filename, invoiceDate) {
		$.ajax({
			url: 'includes/invoices/get_cash_registers.php',
			type: 'GET',
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					cashRegistersList = response.cash_registers; // store the full list
					populateCashRegisterFilter(cashRegistersList);
					
					// Load items for "all" by default
					if (currentFilename && $('#viewInvoiceModal').data('invoice-date')) {
						showLoadingState();
						loadDailyItems(
							currentFilename,
							$('#viewInvoiceModal').data('invoice-date'),
							null
						);
					}
					} else {
					showError(response.message || translations.error_loading_cash_registers);
				}
			}
		});
	}
	
	
	function populateCashRegisterFilter(cashRegisters) {
		const $select = $('#cashRegisterFilter');
		$select.empty();
		
		$select.append(`<option value="all">${translations.all}</option>`);
		
		if (cashRegisters && cashRegisters.length > 0) {
			cashRegisters.forEach(register => {
				$select.append(
					`<option value="${register.code}">${register.text}</option>`
				);
			});
		}
		
		if (!$select.hasClass('select2-hidden-accessible')) {
			$select.select2({ width: '100%' });
		}
		
		$select.val('all').trigger('change.select2');
	}
	
	// Function to load daily items with optional cash register filter
	function loadDailyItems(filename, invoiceDate, cashRegister = null, cashRegisterName = null) {
		$.ajax({
			url: 'includes/invoices/get_invoice_details.php',
			type: 'GET',
			data: { filename, cash_register: cashRegister },
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					hideLoadingState();
					currentDailyData = response.data;
					populateInvoiceModal(response.data, invoiceDate, cashRegisterName);
					} else {
					showError(response.message || translations.error_loading_data);
				}
			}
		});
	}
	
    
    // Function to show loading state
	function showLoadingState() {
		$('#loadingState').removeClass('d-none');
		$('#dailyItemsWrapper').addClass('d-none');
		$('#emptyState').addClass('d-none');
	}
	
	function hideLoadingState() {
		$('#loadingState').addClass('d-none');
		$('#dailyItemsWrapper').removeClass('d-none');
	}
    
    // Function to populate the modal with daily items
	function populateInvoiceModal(dailyData, invoiceDate = null, selectedCashRegister = null) {
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
			if (!dateString) return translations.unknown_date || 'Unknown date';
			
			// First, try to parse the date string
			var date;
			
			// Check if it's already a Date object or a string that can be parsed
			if (dateString instanceof Date) {
				date = dateString;
				} else if (typeof dateString === 'string') {
				// Try different date formats that might come from the database
				date = new Date(dateString);
				
				// If that fails, try adding time component
				if (isNaN(date.getTime())) {
					date = new Date(dateString + 'T00:00:00');
				}
				
				// If still fails, return the original string
				if (isNaN(date.getTime())) {
					return dateString;
				}
				} else {
				return dateString;
			}
			
			// Format based on language
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
		
		// Use the passed invoiceDate if available, otherwise use dailyData.date
		var actualDate = invoiceDate || dailyData.date;
		
		// Update summary information
		var displayDate = formatDisplayDate(actualDate, userLang);
		$('#modalDate').text(displayDate);
		$('#modalDailyTotal').text(formatCurrency(dailyData.daily_total, userLang) + " RSD");
		
		// Update modal title
		var title = `${translations.sold_items_this_day}: ${displayDate}`;
		if (selectedCashRegister) {
			title += ` - ${translations.cash_register}: ${selectedCashRegister}`;
		}		
		$('#viewInvoiceModalLabel').html(`<i class="bi bi-list-check me-2"></i>${title}`);
		
		// Populate items table
		var itemsBody = $('#dailyItemsBody');
		var emptyState = $('#emptyState');
		var tableWrapper = $('#dailyItemsWrapper');
		
		itemsBody.empty();
		
		if (dailyData.items && dailyData.items.length > 0) {
			emptyState.addClass('d-none');
			tableWrapper.removeClass('d-none');
			
			var grandTotal = 0;
			dailyData.items.forEach(function(item, index) {
				var itemTotal = item.TotalAmount || (item.UnitPrice * item.Quantity) || 0;
				grandTotal += itemTotal;
				
				var row = `
				<tr>
                <td>${index + 1}</td>
                <td>
				<div class="item-name" title="${item.Name || ''}">${item.Name || translations.unknown_item}</div>
                </td>
                <td class="text-end">
				<span class="number-cell">${formatNumber(item.Quantity, userLang)}</span>
                </td>
                <td class="text-end">
				<span class="number-cell">${formatCurrency(item.UnitPrice, userLang)} RSD</span>
                </td>
                <td class="text-end fw-semibold">
				<span class="number-cell">${formatCurrency(itemTotal, userLang)} RSD</span>
                </td>
				</tr>
				`;
				itemsBody.append(row);
			});
			
			// Update grand total
			$('#grandTotal').text(formatCurrency(grandTotal, userLang) + " RSD");
			
			} else {
			tableWrapper.addClass('d-none');
			emptyState.removeClass('d-none');
		}
	}
    
    // Function to show error messages
    function showError(message) {
		$('#modalBodyContent').html(`
			<div id="errorState" class="text-center py-5">
            <div class="alert alert-danger" role="alert">
			<i class="bi bi-exclamation-triangle-fill me-2"></i>
			${message}
            </div>
            <button type="button" class="btn btn-primary mt-3" onclick="location.reload()">
			<i class="bi bi-arrow-clockwise me-1"></i> ${translations.try_again}
            </button>
			</div>
		`);
	}
	
	$('#cashRegisterFilter')
    .off('change')
    .on('change', function () {
        let selectedRegister = $(this).val();
        let registerName = null;
		if (selectedRegister && selectedRegister !== 'all') {
			const reg = cashRegistersList.find(r => r.code === selectedRegister);
			registerName = reg ? reg.name : selectedRegister;
		}
		
        // Update modal title
        const displayDate = $('#modalDate').text();
		let title = `${translations.sold_items_this_day}: ${displayDate}`;
		if (registerName) {
			title += ` - ${translations.cash_register}: ${registerName}`;
		}
		$('#viewInvoiceModalLabel').html(`<i class="bi bi-list-check me-2"></i>${title}`);
		
        // Load items
        if (currentFilename && $('#viewInvoiceModal').data('invoice-date')) {
            showLoadingState();
            loadDailyItems(
				currentFilename,
				$('#viewInvoiceModal').data('invoice-date'),
				selectedRegister === 'all' ? null : selectedRegister,
				registerName // <-- new parameter
			);
		}
	});
	
	// Clear modal data when it's hidden
	$(document).on('hidden.bs.modal', '#viewInvoiceModal', function () {
		$(this).removeData('invoice-date');
		currentDailyData = null;
		currentFilename = null;
		
		var $select = $('#cashRegisterFilter');
		if ($select.data('select2')) {
			$select.val('all').trigger('change.select2');
			} else {
			$select.val('all');
		}
		
		$('#modalDate').text('');
		$('#modalDailyTotal').text('0.00');
		$('#dailyItemsBody').empty();
		$('#grandTotal').text('0.00');
		
		$('#viewInvoiceModalLabel').html(
			`<i class="bi bi-list-check me-2"></i>${translations.daily_items}`
		);
		
		hideLoadingState();
	});
	
    
});