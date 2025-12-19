<?php
	
	include_once '../dbconnect.php';
	include_once '../auth.php';
	
	header('Content-Type: application/json');
	
	$response = ['success' => false, 'message' => ''];
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$full_name = $_POST['full_name'];
		$username = $_POST['username'];
		$email = $_POST['email'];
		$password = $_POST['password'];
		$active = isset($_POST['active']) ? (int)$_POST['active'] : 1;
		$image_name = '';
		$image_uploaded = false;
		
		// Check if username already exists
		$checkUserQuery = "SELECT id FROM users WHERE username = ? LIMIT 1";
		$checkStmt = $conn->prepare($checkUserQuery);
		$checkStmt->bind_param("s", $username);
		$checkStmt->execute();
		$checkStmt->store_result();
		
		if ($checkStmt->num_rows > 0) {
			$response['message'] = 'username_exists'; // Send translation key
			echo json_encode($response);
			exit;
		}
		$checkStmt->close();
		
		// Permission check for non-admins: permission_id 1 assumed for user creation
		if (!$sup_admin) {
			$checkQuery = "SELECT 1 FROM users_permissions WHERE user_id = ? AND permission_id = 1 LIMIT 1";
			$checkStmt = $conn->prepare($checkQuery);
			$checkStmt->bind_param("i", $user_id);
			$checkStmt->execute();
			$checkStmt->store_result();
			
			if ($checkStmt->num_rows === 0) {
				http_response_code(403);
				die(json_encode(['success' => false, 'message' => 'unauthorized']));
			}
			$checkStmt->close();
		}
		
		// Start transaction
		$conn->begin_transaction();
		
		try {
			// Hash the password before insert (simple example)
			$password_hash = password_hash($password, PASSWORD_DEFAULT);
			
			// Handle the image upload
			if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
				$allowed_types = ['image/webp'];
				$file_type = $_FILES['profile_image']['type'];
				if (in_array($file_type, $allowed_types)) {
					$image_dir = '../../../images/users/';
					$image_name = uniqid('', true) . '.webp';
					$image_path = $image_dir . $image_name;
					
					if (!is_dir($image_dir)) {
						mkdir($image_dir, 0777, true);
					}
					
					if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $image_path)) {
						throw new Exception('image_upload_failed.');
					}
					$image_uploaded = true;
				} 
				else {
					throw new Exception('invalid_image_type');
				}
			}
			
			$query = "INSERT INTO users (name, username, email, password, profile_image, active) VALUES (?, ?, ?, ?, ?, ?)";
			
			if ($stmt = $conn->prepare($query)) {
				$stmt->bind_param('sssssi', $full_name, $username, $email, $password_hash, $image_name, $active);
				
				if ($stmt->execute()) {
					$user_id = $conn->insert_id;
					$conn->commit();
					
					$response['success'] = true;
					$response['message'] = 'User created successfully!';
					$response['user_id'] = $user_id;
					} else {
					throw new Exception('database_error');
				}
				$stmt->close();
			} 
			else {
				throw new Exception('database_error');
			}
			
			} catch (Exception $e) {
			// Rollback transaction on any error
			$conn->rollback();
			
			// Clean up uploaded file if transaction failed
			if ($image_uploaded && file_exists($image_path)) {
				unlink($image_path);
			}
			
			$response['message'] = $e->getMessage();
		}
	} 
	else {
		$response['message'] = 'Invalid request method.';
	}
	
	echo json_encode($response);
?>