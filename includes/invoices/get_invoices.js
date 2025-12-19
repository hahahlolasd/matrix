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
	
    var table = $('#invoices_table').DataTable({
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
					<i class="bi bi-eye dt-icon view-icon" data-id="${row.id}" title="${translations.view}"></i>
					<i class="bi bi-trash dt-icon delete-icon" data-id="${row.id}" title="${translations.delete}"></i>
					`;
				}
			},
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
	
});