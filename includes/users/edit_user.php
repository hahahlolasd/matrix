<?php
	include_once '../dbconnect.php';
	include_once '../auth.php';
	
	// === Check if the user has permission 2 OR is super admin ===
	if (!$sup_admin && !in_array(2, $permissions)) {
		echo json_encode(['success' => false, 'message' => 'Permission denied']);
		exit;
	}
	
	if (!isset($_POST['user_id'])) {
		echo json_encode(['success' => false, 'message' => 'User ID missing']);
		exit;
	}
	
	$user_id = intval($_POST['user_id']);
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$name = trim($_POST['name'] ?? '');
		$email = trim($_POST['email'] ?? '');
		$username = trim($_POST['username'] ?? '');
		$active = intval($_POST['active'] ?? 0);
		$password = $_POST['password'] ?? null;
		$permissions = $_POST['permissions'] ?? []; // array or empty
		
		// Basic validation
		if (empty($name) || empty($username)) {
			echo json_encode(['success' => false, 'message' => 'Name and username are required']);
			exit;
		}
		
		// Start transaction
		$conn->begin_transaction();
		
		try {
			// Get old profile image filename
			$old_image_stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
			$old_image_stmt->bind_param("i", $user_id);
			$old_image_stmt->execute();
			$old_image_result = $old_image_stmt->get_result();
			$old_image = null;
			if ($old_image_result->num_rows > 0) {
				$old_user = $old_image_result->fetch_assoc();
				$old_image = $old_user['profile_image'];
			}
			$old_image_stmt->close();
			
			// Handle profile image upload
			$profile_image_name = null;
			$upload_dir = '../../../images/users/';
			if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
				// Generate unique filename
				$file_extension = 'webp';
				$profile_image_name = uniqid() . '_' . time() . '.' . $file_extension;
				$upload_path = $upload_dir . $profile_image_name;
				
				// Move uploaded file
				if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
					throw new Exception('Failed to upload profile image');
				}
			}
			
			// Update user basic info
			if ($password !== null && $password !== '') {
				$password_hash = password_hash($password, PASSWORD_DEFAULT);
				
				if ($profile_image_name) {
					// Update with password and profile image
					$updateUserStmt = $conn->prepare("
					UPDATE users SET
					name = ?, email = ?, username = ?, active = ?, 
					password = ?, profile_image = ?, updated_at = NOW()
					WHERE id = ?
					");
					$updateUserStmt->bind_param("sssissi", $name, $email, $username, $active, $password_hash, $profile_image_name, $user_id);
					} else {
					// Update with password only
					$updateUserStmt = $conn->prepare("
					UPDATE users SET
					name = ?, email = ?, username = ?, active = ?, 
					password = ?, updated_at = NOW()
					WHERE id = ?
					");
					$updateUserStmt->bind_param("sssisi", $name, $email, $username, $active, $password_hash, $user_id);
				}
				} else {
				if ($profile_image_name) {
					// Update with profile image only (no password change)
					$updateUserStmt = $conn->prepare("
					UPDATE users SET
					name = ?, email = ?, username = ?, active = ?, 
					profile_image = ?, updated_at = NOW()
					WHERE id = ?
					");
					$updateUserStmt->bind_param("sssisi", $name, $email, $username, $active, $profile_image_name, $user_id);
					} else {
					// Update without password and without profile image - FIXED: 5 parameters
					$updateUserStmt = $conn->prepare("
					UPDATE users SET
					name = ?, email = ?, username = ?, active = ?, 
					updated_at = NOW()
					WHERE id = ?
					");
					$updateUserStmt->bind_param("sssii", $name, $email, $username, $active, $user_id); // Changed to "sssii"
				}
			}
			
			if (!$updateUserStmt->execute()) {
				throw new Exception('Error updating user: ' . $conn->error);
			}
			$updateUserStmt->close();
			
			// Synchronize permissions
			$delStmt = $conn->prepare("DELETE FROM users_permissions WHERE user_id = ?");
			$delStmt->bind_param("i", $user_id);
			if (!$delStmt->execute()) {
				throw new Exception('Error deleting old permissions');
			}
			$delStmt->close();
			
			if (is_array($permissions) && count($permissions) > 0) {
				$insertStmt = $conn->prepare("INSERT INTO users_permissions (user_id, permission_id) VALUES (?, ?)");
				foreach ($permissions as $perm_id) {
					$perm_id_int = intval($perm_id);
					$insertStmt->bind_param("ii", $user_id, $perm_id_int);
					if (!$insertStmt->execute()) {
						throw new Exception('Error inserting permissions');
					}
				}
				$insertStmt->close();
			}
			
			// Commit transaction
			$conn->commit();
			
			// Delete old image only after successful transaction commit
			if ($profile_image_name && !empty($old_image) && file_exists($upload_dir . $old_image)) {
				unlink($upload_dir . $old_image);
			}
			
			echo json_encode(['success' => true, 'message' => 'User updated successfully']);
			
			} catch (Exception $e) {
			// Rollback transaction on any error
			$conn->rollback();
			
			// Delete the newly uploaded image if transaction failed
			if (isset($profile_image_name) && file_exists($upload_path)) {
				unlink($upload_path);
			}
			
			echo json_encode(['success' => false, 'message' => $e->getMessage()]);
		}
		
		exit;
	}
?>