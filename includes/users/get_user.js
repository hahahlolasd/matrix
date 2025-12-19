document.addEventListener('DOMContentLoaded', function () {
	const userId = localStorage.getItem('selectedEditUserId');
	if (userId) {
		loadUserData(userId);
	}
});

function getLanguageFromCookie() {
	const match = document.cookie.match(/(^|;)\s*lang=([^;]+)/);
	return match ? match[2] : 'en';
}

function getLocalizedCategory(catKey, lang) {
	// Use the translations object with fallback to the category key
	return translations[catKey] || catKey;
}

function loadUserData(userId) {
	LoadingLine.start({ disable: '#saveButton' });
	
	$.ajax({
		url: 'includes/users/get_user.php',
		type: 'POST',
		data: { item_id: userId },
		dataType: 'json',
		success: function (response) {
			console.log(response); // debug
			
			if (response.success && response.data) {
				const user = response.data;
				const groupedPermissions = response.grouped_permissions;
				const lang = getLanguageFromCookie();
				
				$('#full_name').val(user.name);
				$('#username').val(user.username);
				$('#email').val(user.email);
				
				const activeSelect = $('#active');
				activeSelect.empty().append(new Option(translations.yes, 1)).append(new Option(translations.no, 0));
				activeSelect.val(user.active).trigger('change');
				
				[activeSelect].forEach(select => {
					if (select.hasClass('select2-hidden-accessible')) {
						select.select2('destroy');
					}
					select.select2({ width: '100%' });
				});
				
				// Display profile image if exists in the placeholder
				const profileImagePlaceholder = $('#profile_image_placeholder');
				if (user.profile_image) {
					profileImagePlaceholder.html(`
						<div class="mb-3 text-center">
						<img src="../images/users/${user.profile_image}" alt="Profile Image" />
						</div>
					`);
					} else {
					// Clear the placeholder if no image exists
					profileImagePlaceholder.empty();
				}
				
				const container = $('#permissions');
				container.empty();
				
				for (const category in groupedPermissions) {
					const localizedCat = getLocalizedCategory(category, lang);
					
					// Create clickable category title with inner span
					const categoryTitle = $(`<h6 class="mt-2 mb-2 permission-category-title" data-category="${category}"><span>${localizedCat}</span></h6>`);
					container.append(categoryTitle);
					
					// Create row for this category's permissions
					const categoryRow = $('<div class="row mb-3"></div>');
					categoryRow.attr('data-category', category);
					container.append(categoryRow);
					
					groupedPermissions[category].forEach(perm => {
						const isChecked = user.permission_ids.includes(perm.id);
						const checkbox = `
						<div class="col-3">
						<div class="form-check">
						<input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" id="perm_${perm.id}" value="${perm.id}" ${isChecked ? 'checked' : ''} data-category="${category}">
						<label class="form-check-label" for="perm_${perm.id}">${perm.name}</label>
						</div>
						</div>`;
						categoryRow.append(checkbox);
					});
				}
				
				// Add click event for category titles - FIXED SELECTOR
				container.on('click', '.permission-category-title', function() {
					const category = $(this).data('category');
					const checkboxes = $(`.permission-checkbox[data-category="${category}"]`);
					
					if (checkboxes.length > 0) {
						// Check if all are checked
						const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
						
						// Toggle all checkboxes in this category
						checkboxes.prop('checked', !allChecked).trigger('change');
					}
				});
				
				// Add hover effects for category titles - USING CSS CLASSES
				container.on('mouseenter', '.permission-category-title span', function() {
					$(this).addClass('category-title-hover');
					}).on('mouseleave', '.permission-category-title span', function() {
					$(this).removeClass('category-title-hover');
				});
				
				LoadingLine.finish();
				} else {
				LoadingLine.fail();
				showDynamicIsland(translations.ajax_error, 'error');
			}
		},
		error: function () {
			LoadingLine.fail();
			showDynamicIsland(translations.ajax_error, 'error');
		},
		complete: function() {
			$('#saveButton').prop('disabled', false);
		}
	});
}