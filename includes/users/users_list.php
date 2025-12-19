<?php
	include_once '../dbconnect.php';
	include_once '../auth.php';
	
	header('Content-Type: application/json; charset=utf-8');
	
	$search = $_GET['search'] ?? '';
	$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 1; // Default to 1 for testing
	
	// Validate page and per_page
	$page = max(1, $page);
	$per_page = max(1, $per_page);
	
	$language = $_COOKIE['lang'] ?? 'hu';
	if (!in_array($language, ['hu', 'sr'])) $language = 'hu';
	
	$search = mysqli_real_escape_string($conn, $search);
	
	// Get all available permissions with categories
	$permissions_sql = "SELECT id, name_{$language} as name, category 
	FROM permissions 
	ORDER BY id";
	$permissions_result = mysqli_query($conn, $permissions_sql);
	
	$available_permissions = [];
	$permissions_by_category = [];
	
	if ($permissions_result) {
		while ($perm_row = mysqli_fetch_assoc($permissions_result)) {
			$category = $perm_row['category'] ? $perm_row['category'] : 'general';
			
			$permission_data = [
			'id' => (int)$perm_row['id'],
			'name' => $perm_row['name'],
			'category' => $category
			];
			
			$available_permissions[] = $permission_data;
			
			// Group by category
			if (!isset($permissions_by_category[$category])) {
				$permissions_by_category[$category] = [
				'category_name' => $category,
				'permissions' => []
				];
			}
			$permissions_by_category[$category]['permissions'][] = $permission_data;
		}
	}
	
	// Sort permissions within each category by ID
	foreach ($permissions_by_category as $category => $category_data) {
		usort($permissions_by_category[$category]['permissions'], function($a, $b) {
			return $a['id'] - $b['id'];
		});
	}
	
	// Base SQL for counting total records
	$count_sql = "
	SELECT COUNT(DISTINCT u.id) as total
	FROM users u
	LEFT JOIN users_permissions up ON up.user_id = u.id
	LEFT JOIN permissions p ON p.id = up.permission_id
	";
	
	// Base SQL for fetching users
	$sql = "
	SELECT 
	u.id,
	u.name,
	u.username,
	u.email,
	u.profile_image,
	u.sup_admin,
	u.active,
	u.last_login,
	u.created_at
	FROM users u
	";
	
	// Add search conditions to both queries
	if (!empty($search)) {
		$where_condition = " WHERE (
		u.id LIKE '%$search%' OR
		u.name LIKE '%$search%' OR
		u.username LIKE '%$search%'
		)";
		
		$count_sql .= $where_condition;
		$sql .= $where_condition;
	}
	
	// Add ORDER BY to main query
	$sql .= " ORDER BY u.active DESC, u.id ASC";
	
	// Add pagination to main query
	$offset = ($page - 1) * $per_page;
	$sql .= " LIMIT $per_page OFFSET $offset";
	
	// Get total count
	$count_result = mysqli_query($conn, $count_sql);
	$total_count = 0;
	if ($count_result) {
		$count_row = mysqli_fetch_assoc($count_result);
		$total_count = (int)$count_row['total']; // Ensure integer
	}
	
	// Calculate total pages
	$total_pages = ceil($total_count / $per_page);
	
	// Get paginated users
	$result = mysqli_query($conn, $sql);
	
	$users = [];
	if ($result) {
		while ($row = mysqli_fetch_assoc($result)) {
			// Get permissions for this specific user
			$user_id = (int)$row['id'];
			$user_perms_sql = "
			SELECT p.id, p.name_{$language} as name 
			FROM users_permissions up 
			LEFT JOIN permissions p ON p.id = up.permission_id 
			WHERE up.user_id = $user_id
			ORDER BY p.id
			";
			$user_perms_result = mysqli_query($conn, $user_perms_sql);
			
			$user_permissions = [];
			if ($user_perms_result) {
				while ($perm_row = mysqli_fetch_assoc($user_perms_result)) {
					$user_permissions[] = [
					'id' => (int)$perm_row['id'],
					'name' => $perm_row['name']
					];
				}
			}
			
			$users[] = [
			'id' => (int)$row['id'],
			'name' => $row['name'],
			'username' => $row['username'],
			'email' => $row['email'],
			'profile_image' => $row['profile_image'],
			'sup_admin' => (bool)$row['sup_admin'],
			'active' => (bool)$row['active'],
			'last_login' => $row['last_login'],
			'created_at' => $row['created_at'],
			'permissions' => $user_permissions
			];
		}
	}
	
	// Return response with pagination info and separate permissions arrays
	$response = [
	'users' => $users,
	'available_permissions' => $available_permissions,
	'permissions_by_category' => $permissions_by_category,
	'pagination' => [
	'current_page' => (int)$page,
	'per_page' => (int)$per_page,
	'total_users' => (int)$total_count,
	'total_pages' => (int)$total_pages
	]
	];
	
	echo json_encode($response);
?>