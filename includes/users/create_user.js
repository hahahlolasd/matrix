$(document).ready(function() {
	
	$('.select2').select2({
		width: '100%'
	});
	
	// === ADD THIS PASSWORD VALIDATION CODE ===
	function validatePasswords() {
		const password = $('#password').val();
		const confirmPassword = $('#confirm_password').val();
		
		if (password !== confirmPassword) {
			showDynamicIsland(translations.passwords_do_not_match, "error");
			return false;
		}
		return true;
	}
	// === END PASSWORD VALIDATION CODE ===
	
	$('#createUser').on('submit', function(event) {
		event.preventDefault();
		
		// === ADD THIS VALIDATION CHECK ===
		if (!validatePasswords()) {
			return; // Stop the form submission
		}
		// === END VALIDATION CHECK ===
		
		var formData = new FormData(this);
		var saveButton = $('#saveButton');
		var profileImage = $('#profile_image')[0].files[0];
		
		saveButton.prop('disabled', true);
		
		// Use new LoadingLine pattern
		LoadingLine.start({ disable: '#saveButton' });
		
		// Process image if exists, then upload - pass saveButton as parameter
		if (profileImage) {
			processImage(profileImage, 800, function(processedBlob) {
				if (processedBlob) {
					formData.set('profile_image', processedBlob, 'profile_image.webp');
				}
				uploadUserData(formData, saveButton);
			});
			} else {
			uploadUserData(formData, saveButton);
		}
	});
	
	// Accept saveButton as parameter
	function uploadUserData(formData, saveButton) {
		$.ajax({
			url: 'includes/users/create_user.php',
			type: 'POST',
			data: formData,
			contentType: false,
			processData: false,
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					showDynamicIsland(translations.user + " " + translations.create_success, "upload");
					
					// Refresh users list first using the global function
					if (typeof loadUsers === 'function') {
						loadUsers(); // This will refresh the user cards grid
					}
					
					// Destroy the first form
					$('#createUser').remove();
					
					// Insert the second form with permissions
					generateUserPermissions(response.user_id); 
					} else {
					// Show the specific error message from PHP
					if (response.message) {
						// Use the translation key directly from PHP response
						showDynamicIsland(translations[response.message] || response.message, "error");
						} else {
						showDynamicIsland(translations.user + " " + translations.create_error, "error");
					}
					saveButton.prop('disabled', false);
					LoadingLine.finish();
				}
			},
			error: function(xhr, status, error) {
				showDynamicIsland(translations.ajax_error, "error");
				saveButton.prop('disabled', false);
				LoadingLine.fail();
			}
		});
	}
	
	function generateUserPermissions(user_id) {
		const categoryIcons = {
			users: 'person-lines-fill',
			indirect_content: 'journal-text',
			news: 'calendar3',
			ads: 'badge-ad-fill',
			categories: 'tags-fill',
			cities: 'buildings-fill',
			electricity_types: 'lightning-fill',
			heating_types: 'thermometer-half',
			locations: 'signpost-2-fill',
			materials: 'bricks',
			outbuildings: 'building-add',
			state_types: 'hand-thumbs-up-fill',
		};
		
		function getLanguageFromCookie() {
			const match = document.cookie.match(/(^|;)\s*lang=([^;]+)/);
			return match ? match[2] : 'hu';
		}
		
		function getLocalizedCategory(catKey) {
			// Use the translations object directly to localize category names
			return translations[catKey] || catKey;
		}
		
		// Start loading for permissions fetch
		LoadingLine.start({ disable: '#savePermissionsButton' });
		
		$.ajax({
			url: 'includes/users/get_permissions.php',
			method: 'GET',
			dataType: 'json',
			success: function (data) {
				LoadingLine.finish();
				
				if (!data.success) {
					showDynamicIsland(translations.permission_fetch_error, "error");
					return;
				}
				
				const grouped = {};
				
				// Group by category
				data.permissions.forEach(p => {
					if (!grouped[p.category]) grouped[p.category] = [];
					grouped[p.category].push(p);
				});
				
				let checkboxesHtml = '';
				for (const cat in grouped) {
					const localizedCat = getLocalizedCategory(cat);
					const iconName = categoryIcons[cat] || 'folder-fill';
					
					checkboxesHtml += `
					<h6 class="my-1 text-white">
					<i class="bi bi-${iconName}" style="margin-right: 6px;"></i>
					${localizedCat}
					</h6><div class="row">`;
					
					grouped[cat].forEach(perm => {
						checkboxesHtml += `
						<div class="col-6">
						<div class="form-check">
						<input class="form-check-input" type="checkbox" id="perm_${perm.id}" name="permissions[]" value="${perm.id}">
						<label class="form-check-label" for="perm_${perm.id}">${perm.name}</label>
						</div>
						</div>`;
					});
					
					checkboxesHtml += '</div>';
				}
				
				const html = `
				<form id="userPermissionsForm">
				<input type="hidden" name="user_id" value="${user_id}">
				<div class="form-group">${checkboxesHtml}</div>
				<div class="text-center mt-3">
				<button id="savePermissionsButton" class="btn btn-primary" type="submit">
				<i class="bi bi-floppy"></i> ${translations.save}
				</button>
				</div>
				</form>`;
				
				$('#formWrapper').html(html);
				
				bindUserPermissionsForm();
			},
			error: function () {
				LoadingLine.fail();
				showDynamicIsland(translations.ajax_error, "error");
			}
		});
	}
	
	function bindUserPermissionsForm() {
		$('#userPermissionsForm').on('submit', function(e) {
			e.preventDefault();
			
			var formData = new FormData(this);
			var saveButton = $('#savePermissionsButton'); // Define locally here too
			saveButton.prop('disabled', true);
			
			// Use new LoadingLine pattern
			LoadingLine.start({ disable: '#savePermissionsButton' });
			
			$.ajax({
				url: 'includes/users/save_user_permissions.php',
				method: 'POST',
				data: formData,
				contentType: false,
				processData: false,
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						showDynamicIsland(translations.permissions + " " + translations.upload_success, "upload");
						setTimeout(() => location.reload(), 1500);
						} else {
						showDynamicIsland(translations.permissions + " " + translations.upload_error, "error");
						saveButton.prop('disabled', false);
					}
					LoadingLine.finish();
				},
				error: function() {
					showDynamicIsland(translations.ajax_error, "error");
					saveButton.prop('disabled', false);
					LoadingLine.fail();
				}
			});
		});
	}
	
});