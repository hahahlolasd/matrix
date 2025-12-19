$(document).ready(function() {
	
	$('#createInvoice').on('submit', function(event) {
		event.preventDefault();
		
		var formData = new FormData(this);
		var saveButton = $('#saveButton');
		var jsonFile = $('#invoice_json')[0].files[0];
		
		// Get the actual selected date from flatpickr instance
		var dateInput = $('#invoice_date')[0];
		var fpInstance = dateInput._flatpickr;
		
		if (fpInstance) {
			// Get the selected date(s)
			var selectedDates = fpInstance.selectedDates;
			
			if (selectedDates.length > 0) {
				// Format the date as YYYY-MM-DD without timezone conversion
				var selectedDate = selectedDates[0];
				
				// Create date string in local timezone
				var year = selectedDate.getFullYear();
				var month = String(selectedDate.getMonth() + 1).padStart(2, '0');
				var day = String(selectedDate.getDate()).padStart(2, '0');
				
				var mysqlDate = year + '-' + month + '-' + day;
				
				// Override the date in FormData
				formData.set('invoice_date', mysqlDate);
			}
		}
		
		// Validate file extension
		if (jsonFile && !jsonFile.name.toLowerCase().endsWith('.json')) {
			showDynamicIsland(translations.invalid_json_file, "error");
			return;
		}
		
		// Disable button and show loading
		saveButton.prop('disabled', true);
		LoadingLine.start({ disable: '#saveButton' });
		
		// Upload the JSON file
		$.ajax({
			url: 'includes/invoices/create_invoice.php',
			type: 'POST',
			data: formData,
			contentType: false,
			processData: false,
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					showDynamicIsland(translations.invoice + " " + translations.upload_success, "upload");
					
					// Refresh invoice list if function exists
					table.ajax.reload();
					
					// Clear the form
					$('#createInvoice')[0].reset();
					
					// Reset dropify
					if ($('.dropify').length) {
						var dropify = $('.dropify').dropify();
						dropify = dropify.data('dropify');
						dropify.resetPreview();
						dropify.clearElement();
					}
					
					// Clear datepicker
					$('#invoice_date').val('');
					
					} else {
					// Show generic upload error
					showDynamicIsland(translations.invoice + " " + translations.upload_error, "error");
					saveButton.prop('disabled', false);
				}
				LoadingLine.finish();
			},
			error: function(xhr, status, error) {
				showDynamicIsland(translations.ajax_error, "error");
				saveButton.prop('disabled', false);
				LoadingLine.fail();
			}
		});
	});
	
});