<?php
	include_once '../dbconnect.php';
	include_once '../auth.php';
	
	// === Check if the user has permission 2 OR is a super admin ===
	if (!$sup_admin && !in_array(2, $permissions)) {
		echo json_encode(['success' => false, 'message' => 'Permission denied']);
		exit;
	}
	
	if (!isset($_POST['item_id'])) {
		echo json_encode(['success' => false, 'message' => 'Item ID missing']);
		exit;
	}
	
	$language = $_COOKIE['lang'] ?? 'en';
	if (!in_array($language, ['hu', 'sr', 'en'])) $language = 'en';
	
	$item_id = intval($_POST['item_id']);
	
	// Fetch user data
	$user_query = $conn->prepare("
	SELECT 
	u.id,
	u.name,
	u.username,
	u.email,
	u.profile_image,
	u.active
	FROM users u
	WHERE u.id = ?
	LIMIT 1
	");
	
	if (!$user_query) {
		echo json_encode(['success' => false, 'message' => 'Query preparation failed']);
		exit;
	}
	
	$user_query->bind_param("i", $item_id);
	$user_query->execute();
	$user_result = $user_query->get_result();
	
	if ($user_result->num_rows === 0) {
		echo json_encode(['success' => false, 'message' => 'User not found']);
		exit;
	}
	
	$user = $user_result->fetch_assoc();
	
	// Fetch all permissions with category
	$all_perm_query = $conn->prepare("
	SELECT id, name_{$language} AS name, category FROM permissions ORDER BY id, category
	");
	$all_perm_query->execute();
	$all_perm_result = $all_perm_query->get_result();
	
	// Group permissions by category
	$grouped_permissions = [];
	while ($row = $all_perm_result->fetch_assoc()) {
		$cat = $row['category'] ?? 'uncategorized';
		if (!isset($grouped_permissions[$cat])) {
			$grouped_permissions[$cat] = [];
		}
		$grouped_permissions[$cat][] = $row;
	}
	
	// Fetch user permission IDs
	$user_perm_ids_query = $conn->prepare("
	SELECT permission_id FROM users_permissions WHERE user_id = ?
	");
	$user_perm_ids_query->bind_param("i", $item_id);
	$user_perm_ids_query->execute();
	$user_perm_ids_result = $user_perm_ids_query->get_result();
	
	$user_permission_ids = [];
	while ($row = $user_perm_ids_result->fetch_assoc()) {
		$user_permission_ids[] = intval($row['permission_id']);
	}
	
	$user['permission_ids'] = $user_permission_ids;
	
	echo json_encode([
	'success' => true,
	'data' => $user,
	'grouped_permissions' => $grouped_permissions
	], JSON_UNESCAPED_UNICODE);
	exit;
