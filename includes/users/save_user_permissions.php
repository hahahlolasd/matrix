<?php
	
	include_once '../dbconnect.php';
	include_once '../auth.php';
	
	header('Content-Type: application/json');
	
	$response = ['success' => false, 'message' => ''];
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
		$permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
		
		if ($user_id <= 0) {
			$response['message'] = 'Invalid user ID.';
			echo json_encode($response);
			exit;
		}
		
		// Permission check for non-superadmins: must have permission_id = 1
		if (!$sup_admin) {
			$checkQuery = "SELECT 1 FROM users_permissions WHERE user_id = ? AND permission_id = 1 LIMIT 1";
			$checkStmt = $conn->prepare($checkQuery);
			$checkStmt->bind_param("i", $user_id);
			$checkStmt->execute();
			$checkStmt->store_result();
			if ($checkStmt->num_rows === 0) {
				http_response_code(403);
				echo json_encode(['success' => false, 'message' => 'Unauthorized']);
				exit;
			}
			$checkStmt->close();
		}
		
		// Remove existing permissions for the user
		$deleteQuery = "DELETE FROM users_permissions WHERE user_id = ?";
		$deleteStmt = $conn->prepare($deleteQuery);
		$deleteStmt->bind_param("i", $user_id);
		$deleteStmt->execute();
		$deleteStmt->close();
		
		// Insert new permissions
		if (!empty($permissions)) {
			$insertQuery = "INSERT INTO users_permissions (user_id, permission_id) VALUES ";
			$insertData = [];
			$types = '';
			foreach ($permissions as $perm_id) {
				$insertData[] = $user_id;
				$insertData[] = (int)$perm_id;
				$types .= 'ii';
			}
			
			$values = implode(',', array_fill(0, count($permissions), '(?, ?)'));
			$stmt = $conn->prepare($insertQuery . $values);
			$stmt->bind_param($types, ...$insertData);
			$response['success'] = $stmt->execute();
			$stmt->close();
			
			if (!$response['success']) {
				$response['message'] = 'Failed to insert permissions.';
				echo json_encode($response);
				exit;
			}
		}
		
		$response['success'] = true;
		$response['message'] = 'Permissions updated.';
		} else {
		$response['message'] = 'Invalid request method.';
	}
	
	echo json_encode($response);
