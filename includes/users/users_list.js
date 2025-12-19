// Function to escape HTML to prevent XSS
function escapeHtml(unsafe) {
	if (unsafe === null || unsafe === undefined) return '';
	return unsafe
	.toString()
	.replace(/&/g, "&amp;")
	.replace(/</g, "&lt;")
	.replace(/>/g, "&gt;")
	.replace(/"/g, "&quot;")
	.replace(/'/g, "&#039;");
}

// Global variables for pagination
let currentPage = 1;
let totalPages = 1;
const usersPerPage = 18; // For testing - 1 user per page

// Global variable to store available permissions
let availablePermissions = [];

// Make loadUsers globally accessible
function loadUsers(search = '', page = 1) {
    let failed = false;
    currentPage = page;
    LoadingLine.start({ disable: '#users_search' });
    
    $.ajax({
        url: 'includes/users/users_list.php',
        type: 'GET',
        data: { 
            search: search,
            page: page,
            per_page: usersPerPage
		},
        dataType: 'json',
        success: function(response) {
            // Check if response has pagination data or is just the users array
            if (response.users !== undefined && response.pagination !== undefined) {
                // New format with pagination and permissions
                displayUsers(response.users);
                updatePagination(response.pagination);
                
                // Store available permissions globally
                if (response.available_permissions) {
                    availablePermissions = response.available_permissions;
				}
				} else {
                // Old format - assume it's just the users array
                displayUsers(response);
                // For backward compatibility, we'll create basic pagination info
                updatePagination({
                    current_page: page,
                    total_pages: response.length > 0 ? Math.ceil(response.length / usersPerPage) : 1,
                    total_users: response.length
				});
			}
		},
        error: function(xhr, status, error) {
            failed = true;
            console.error('Error loading users:', error);
            $('#cardsGrid').html(`
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>${translations.error_loading_users}: ${escapeHtml(error)}</div>
                </div>
			`);
            LoadingLine.fail();
		},
        complete: function() {
            if (!failed) {
                LoadingLine.finish();
			}
            $('#users_search').prop('disabled', false);
		}
	});
}

// Make displayUsers globally accessible too
function displayUsers(users) {
    const cardsGrid = $('#cardsGrid');
    
    if (users.length === 0) {
        cardsGrid.html(`
            <div class="text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">${translations.no_records_found}</h4>
            </div>
		`);
        return;
	}
    
    let cardsHTML = '<div class="row g-3">'; // smaller gutter for compact layout
    
    users.forEach(user => {
        // Updated to handle the new permissions array structure
        const permissionsText = user.permissions && user.permissions.length > 0 ? 
		user.permissions.map(p => p.name).join(', ') : '-';
        const lastLogin = user.last_login ? new Date(user.last_login).toLocaleDateString() : '-';
        const createdAt = user.created_at ? new Date(user.created_at).toLocaleDateString() : '-';
        
        // Add active/inactive class to the card
        const cardClass = user.active == 1 ? 'active-card' : 'inactive-card';
        
        cardsHTML += `
        <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-2">
		<div class="card user-card h-100 shadow-sm text-center p-2 ${cardClass}">
		<img src="../images/users/${user.profile_image || 'default.webp'}" class="rounded-circle mx-auto mb-2" alt="${escapeHtml(user.name)}">
		<h6 class="card-title mb-1">${escapeHtml(user.name)}</h6>
		<p class="text-muted mb-1 small">@${escapeHtml(user.username)}</p>
		<div class="mb-1 user-icons">
		${user.active == 1 ? 
			'<i class="bi bi-check-circle-fill text-success" title="' + translations.active + '"></i>' : 
			'<i class="bi bi-x-circle-fill text-danger" title="' + translations.inactive + '"></i>'
		}
		${user.sup_admin == 1 ? 
			'<i class="bi bi-shield-lock-fill text-primary" title="' + translations.super_admin + '"></i>' : 
			''
		}
		</div>
		<div class="card-buttons d-flex justify-content-center gap-1 mt-auto">
		<button class="btn btn-sm btn-outline-primary edit-icon" data-user-id="${user.id}" title="${translations.edit}">
		<i class="bi bi-pencil"></i>
		</button>
		<button class="btn btn-sm btn-outline-info permissions-btn" data-user-id="${user.id}" data-user-name="${escapeHtml(user.name)}" title="${translations.permissions}">
		<i class="bi bi-shield-check"></i>
		</button>
		${user.active == 1 ?
			'<button class="btn btn-sm btn-outline-danger toggle-active-btn" data-user-id="' + user.id + '" data-user-name="' + escapeHtml(user.name) + '" data-current-active="1" title="' + translations.deactivate + '"><i class="bi bi-x-circle-fill"></i></button>' :
			'<button class="btn btn-sm btn-outline-success toggle-active-btn" data-user-id="' + user.id + '" data-user-name="' + escapeHtml(user.name) + '" data-current-active="0" title="' + translations.activate + '"><i class="bi bi-check-circle-fill"></i></button>'
		}
		</div>
		</div>
        </div>
        `;
	});
    
    cardsHTML += '</div>';
    cardsGrid.html(cardsHTML);
}

// Function to show permissions dialog
function showPermissionsDialog(userId, userName) {
    // Icon map for permission categories
    const categoryIcons = {
        'users': 'bi-people-fill',
        'ads': 'bi-badge-ad',
        'categories': 'bi-tags',
        'cities': 'bi-buildings',
        'electricity_types': 'bi-lightning-fill',
        'heating_types': 'bi-thermometer-half',
        'locations': 'bi-signpost-2',
        'materials': 'bi-bricks',
        'outbuildings': 'bi-building-add',
        'state_types': 'bi-hand-thumbs-up',
	};
	
    // Find the user data
    const searchTerm = $('#users_search').val();
    
    // Reload data to ensure we have the latest permissions
    LoadingLine.start();
    
    $.ajax({
        url: 'includes/users/users_list.php',
        type: 'GET',
        data: { 
            search: searchTerm,
            page: currentPage,
            per_page: usersPerPage
		},
        dataType: 'json',
        success: function(response) {
            LoadingLine.finish();
            
            // Update available permissions if provided
            if (response.available_permissions) {
                availablePermissions = response.available_permissions;
			}
            
            // Find the specific user
            const user = response.users.find(u => u.id == userId);
            if (!user) {
                console.error('User not found');
                return;
			}
            
            // Get user's permission IDs for quick lookup
            const userPermissionIds = user.permissions ? user.permissions.map(p => p.id) : [];
            
			// Create dialog HTML
			let dialogHTML = `
			<div class="modal fade" id="permissionsModal" tabindex="-1" aria-labelledby="permissionsModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-xl">
			<div class="modal-content">
			<div class="modal-header">
			<h5 class="modal-title" id="permissionsModalLabel">
			<i class="bi bi-shield-check me-2"></i>
			${escapeHtml(userName)} ${translations.users_permissions} 
			</h5>
			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="${translations.close}"></button>
			</div>
			<div class="modal-body">
			`;
			
			// Check if we have permissions organized by category
			if (response.permissions_by_category && Object.keys(response.permissions_by_category).length > 0) {
				// Use the categorized permissions
				const permissionsByCategory = response.permissions_by_category;
				
				dialogHTML += `<div class="permissions-grid">`;
				
				// Loop through each category
				Object.values(permissionsByCategory).forEach(category => {
					// Get translated category name from translations object
					const categoryKey = category.category_name;
					const translatedCategoryName = translations[categoryKey] || category.category_name;
					
					// Get icon for this category
					const categoryIcon = categoryIcons[categoryKey] || categoryIcons['default'];
					
					dialogHTML += `
					<div class="permission-category mb-4">
					<h6 class="category-title text-primary">
					<i class="bi ${categoryIcon} me-2"></i>
					${escapeHtml(translatedCategoryName)}
					</h6>
					<div class="row g-2">
					`;
					
					// Loop through permissions in this category
					category.permissions.forEach(permission => {
						const hasPermission = userPermissionIds.includes(permission.id);
						const iconClass = hasPermission ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger';
						const iconTitle = hasPermission ? translations.has_permission : translations.no_permission;
						
						dialogHTML += `
						<div class="col-sm-6 col-md-3">
						<div class="permission-card card h-100">
						<div class="card-body p-3 d-flex align-items-center justify-content-between">
						<span class="permission-name">${escapeHtml(permission.name)}</span>
						<i class="bi ${iconClass} ms-2 flex-shrink-0" title="${iconTitle}"></i>
						</div>
						</div>
						</div>
						`;
					});
					
					dialogHTML += `</div></div>`;
				});
				
				dialogHTML += `</div>`;
				
				} else if (availablePermissions.length > 0) {
				// Fallback: use availablePermissions without categories
				dialogHTML += `
				<div class="row g-2">
				`;
				
				availablePermissions.forEach(permission => {
					const hasPermission = userPermissionIds.includes(permission.id);
					const iconClass = hasPermission ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger';
					const iconTitle = hasPermission ? translations.has_permission : translations.no_permission;
					const cardClass = hasPermission ? 'border-success bg-success bg-opacity-10' : 'border-light';
					
					dialogHTML += `
					<div class="col-sm-6 col-md-4">
					<div class="permission-card card h-100 ${cardClass}">
					<div class="card-body p-3 d-flex align-items-center justify-content-between">
					<span class="permission-name small">${escapeHtml(permission.name)}</span>
					<i class="bi ${iconClass} ms-2 flex-shrink-0" title="${iconTitle}"></i>
					</div>
					</div>
					</div>
					`;
				});
				
				dialogHTML += `</div>`;
				} else {
				// No permissions available
				dialogHTML += `
				<div class="text-center py-3 text-muted">
				<i class="bi bi-shield-slash fs-1 mb-2"></i>
				<p>${translations.no_permissions_available}</p>
				</div>
				`;
			}
			
			dialogHTML += `
			</div>
			<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${translations.close}</button>
			</div>
			</div>
			</div>
			</div>
			`;
            
            // Remove existing modal if any
            $('#permissionsModal').remove();
            
            // Add modal to body and show it
            $('body').append(dialogHTML);
            const permissionsModal = new bootstrap.Modal(document.getElementById('permissionsModal'));
            permissionsModal.show();
            
            // Remove modal from DOM when hidden
            $('#permissionsModal').on('hidden.bs.modal', function () {
                $(this).remove();
			});
		},
        error: function(xhr, status, error) {
            LoadingLine.fail();
            console.error('Error loading permissions:', error);
		}
	});
}

// Function to update pagination controls
function updatePagination(pagination) {
    // Ensure all values are numbers
    totalPages = parseInt(pagination.total_pages) || 1;
    currentPage = parseInt(pagination.current_page) || 1;
    const totalUsers = parseInt(pagination.total_users) || 0;
    const perPage = parseInt(pagination.per_page) || usersPerPage;
    
    // Always remove existing pagination container and create a new one
    $('#paginationContainer').remove();
    
    // If there's only one page or no users, don't create pagination container
    if (totalPages <= 1) {
        return;
	}
    
    // Create new pagination container AFTER the cardsGrid
    $('#cardsGrid').after('<div id="paginationContainer" class="d-flex justify-content-center mt-4"></div>');
    
    const paginationContainer = $('#paginationContainer');
    
    let paginationHTML = `
	<nav aria-label="User pagination">
	<ul class="pagination pagination-sm mb-0">
    `;
    
    // First page button (<<)
    if (currentPage > 1) {
        paginationHTML += `
		<li class="page-item">
		<a class="page-link pagination-link" href="#" data-page="1" aria-label="First Page">
		<span aria-hidden="true">&laquo;&laquo;</span>
		</a>
		</li>
        `;
		} else {
        paginationHTML += `
		<li class="page-item disabled">
		<span class="page-link" aria-hidden="true">&laquo;&laquo;</span>
		</li>
        `;
	}
    
    // Previous page button (<)
    if (currentPage > 1) {
        paginationHTML += `
		<li class="page-item">
		<a class="page-link pagination-link" href="#" data-page="${currentPage - 1}" aria-label="Previous Page">
		<span aria-hidden="true">&laquo;</span>
		</a>
		</li>
        `;
		} else {
        paginationHTML += `
		<li class="page-item disabled">
		<span class="page-link" aria-hidden="true">&laquo;</span>
		</li>
        `;
	}
    
    // ALWAYS show page 1
    paginationHTML += `
	<li class="page-item ${currentPage === 1 ? 'active' : ''}">
	${currentPage === 1 ? '<span class="page-link">1</span>' : '<a class="page-link pagination-link" href="#" data-page="1">1</a>'}
	</li>
    `;
    
    // ALWAYS show page 2 if it exists
    if (totalPages >= 2) {
        paginationHTML += `
		<li class="page-item ${currentPage === 2 ? 'active' : ''}">
		${currentPage === 2 ? '<span class="page-link">2</span>' : '<a class="page-link pagination-link" href="#" data-page="2">2</a>'}
		</li>
        `;
	}
    
    // First ellipsis (between page 2 and current page) - only show if there's a gap
    if (currentPage > 3) {
        paginationHTML += `
		<li class="page-item disabled">
		<span class="page-link">...</span>
		</li>
        `;
	}
    
    // Show current page if it's between 3 and totalPages-2 and not already shown
    if (currentPage > 2 && currentPage < totalPages - 1) {
        paginationHTML += `
		<li class="page-item active">
		<span class="page-link">${currentPage}</span>
		</li>
        `;
	}
    
    // Second ellipsis (between current page and last pages) - only show if there's a gap
    if (currentPage < totalPages - 2) {
        paginationHTML += `
		<li class="page-item disabled">
		<span class="page-link">...</span>
		</li>
        `;
	}
    
    // ALWAYS show second-to-last page if it exists and is different from page 2
    if (totalPages >= 3) {
        const secondToLast = totalPages - 1;
        if (secondToLast > 2) { // Only show if it's not page 1 or 2
            paginationHTML += `
			<li class="page-item ${currentPage === secondToLast ? 'active' : ''}">
			${currentPage === secondToLast ? '<span class="page-link">' + secondToLast + '</span>' : '<a class="page-link pagination-link" href="#" data-page="' + secondToLast + '">' + secondToLast + '</a>'}
			</li>
            `;
		}
	}
    
    // ALWAYS show last page if it exists and is different from page 1 and 2
    if (totalPages >= 2) {
        const lastPage = totalPages;
        if (lastPage > 2) { // Only show if it's not page 1 or 2
            paginationHTML += `
			<li class="page-item ${currentPage === lastPage ? 'active' : ''}">
			${currentPage === lastPage ? '<span class="page-link">' + lastPage + '</span>' : '<a class="page-link pagination-link" href="#" data-page="' + lastPage + '">' + lastPage + '</a>'}
			</li>
            `;
		}
	}
    
    // Next page button (>)
    if (currentPage < totalPages) {
        paginationHTML += `
		<li class="page-item">
		<a class="page-link pagination-link" href="#" data-page="${currentPage + 1}" aria-label="Next Page">
		<span aria-hidden="true">&raquo;</span>
		</a>
		</li>
        `;
		} else {
        paginationHTML += `
		<li class="page-item disabled">
		<span class="page-link" aria-hidden="true">&raquo;</span>
		</li>
        `;
	}
    
    // Last page button (>>)
    if (currentPage < totalPages) {
        paginationHTML += `
		<li class="page-item">
		<a class="page-link pagination-link" href="#" data-page="${totalPages}" aria-label="Last Page">
		<span aria-hidden="true">&raquo;&raquo;</span>
		</a>
		</li>
        `;
		} else {
        paginationHTML += `
		<li class="page-item disabled">
		<span class="page-link" aria-hidden="true">&raquo;&raquo;</span>
		</li>
        `;
	}
    
    paginationHTML += `
	</ul>
	</nav>
    `;
    
    // Set the HTML content
    paginationContainer.get(0).innerHTML = paginationHTML;
}

// Function to toggle user active status
function toggleUserActive(userId, userName, currentActive, newActiveStatus) {
    const toggleActiveCallback = function(choice) {
        if (choice) {
            LoadingLine.start();
            
            $.ajax({
                url: 'includes/users/toggle_user_active.php',
                type: 'POST',
                data: { 
                    user_id: userId,
                    active: newActiveStatus
				},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show specific success message based on action
                        if (typeof showDynamicIsland === 'function') {
                            const successMessage = newActiveStatus == 1 ? 
							(translations.user + " " + translations.activated_successfully) :
							(translations.user + " " + translations.deactivated_successfully);
                            showDynamicIsland(successMessage, "success");
							} else {
                            console.log('User status updated successfully');
						}
                        
                        // Reload current page after status change
                        const searchTerm = $('#users_search').val();
                        loadUsers(searchTerm, currentPage);
						} else {
                        // Show specific error message based on action
                        if (typeof showDynamicIsland === 'function') {
                            const errorMessage = newActiveStatus == 1 ? 
							(translations.user + " " + translations.activation_failed) :
							(translations.user + " " + translations.deactivation_failed);
                            showDynamicIsland(errorMessage, "error");
							} else {
                            console.error('Update failed:', response.message);
						}
                        LoadingLine.fail();
					}
				},
                error: function(xhr, status, error) {
                    // Show generic AJAX error
                    if (typeof showDynamicIsland === 'function') {
                        showDynamicIsland(translations.ajax_error, "error");
						} else {
                        console.error('Request error:', error);
					}
                    LoadingLine.fail();
				}
			});
		}
	};
    
    // Show confirmation popup with generic messages
    const confirmMessage = newActiveStatus == 1 ? 
	(translations.confirm_activate_user) :
	(translations.confirm_deactivate_user);
    
    if (typeof notif_confirm === 'function') {
        notif_confirm({
            textaccept: translations.yes || "Yes",
            textcancel: translations.no || "No",
            message: confirmMessage,
            callback: toggleActiveCallback
		});
		} else {
        // Fallback to native confirm
        if (confirm(confirmMessage)) {
            toggleActiveCallback(true);
		}
	}
}

$(function () {
    // Load users when page loads
    loadUsers();
    
    // Search functionality
    $(document).on('keyup', '#users_search', function() {
        const searchTerm = $(this).val();
        clearTimeout($(this).data('timeout'));
        $(this).data('timeout', setTimeout(() => {
            loadUsers(searchTerm, 1); // Reset to page 1 when searching
		}, 500));
	});
    
    // Pagination link clicks
    $(document).on('click', '.pagination-link', function(e) {
        e.preventDefault();
        const page = parseInt($(this).data('page'));
        const searchTerm = $('#users_search').val();
        loadUsers(searchTerm, page);
	});
    
    // Handle "Edit" button clicks - Store user ID in localStorage
    $(document).on('click', '.edit-icon', function(e) {
        e.preventDefault();
        const userId = $(this).data('user-id');
        localStorage.setItem('selectedEditUserId', userId);
        window.location.href = 'edit_user'; // Navigate to edit page
	});
    
    // Handle "Permissions" button clicks
    $(document).on('click', '.permissions-btn', function() {
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');
        showPermissionsDialog(userId, userName);
	});
    
    // Handle toggle active button clicks
    $(document).on('click', '.toggle-active-btn', function() {
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');
        const currentActive = $(this).data('current-active');
        const newActiveStatus = currentActive == 1 ? 0 : 1;
        
        toggleUserActive(userId, userName, currentActive, newActiveStatus);
	});
});