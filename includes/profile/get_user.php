<?php
	include_once '../dbconnect.php';
	include_once '../auth.php'; // ensures $user_id, $sup_admin, $permissions or redirects
	
	header('Content-Type: application/json; charset=utf-8');
	
	// If auth.php didn’t redirect but $user_id somehow missing, bail safely
	if (!isset($user_id) || !is_numeric($user_id)) {
		echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	// Query
	$sql = "
	SELECT 
	u.username,
	u.email,
	u.name,
	u.image,
	u.sup_admin,
	u.created_at,
	u.last_login
	FROM users u
	WHERE u.id = ?
	LIMIT 1
	";
	
	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		echo json_encode(['success' => false, 'message' => 'Query prepare failed']);
		exit;
	}
	
	$stmt->bind_param('i', $user_id);
	$stmt->execute();
	$result = $stmt->get_result();
	
	if ($row = $result->fetch_assoc()) {
		echo json_encode(['success' => true, 'data' => $row], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
		} else {
		echo json_encode(['success' => false, 'message' => 'User not found']);
	}
