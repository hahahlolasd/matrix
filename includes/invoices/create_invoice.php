<?php
	
	include_once '../dbconnect.php';
	include_once '../auth.php';
	
	header('Content-Type: application/json');
	
	$response = ['success' => false, 'message' => ''];
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		// Get form data
		$invoice_date = $_POST['invoice_date'] ?? '';
		$file_uploaded = false;
		$file_name = '';
		
		// Validate required fields
		if (empty($invoice_date)) {
			$response['message'] = 'date_required';
			echo json_encode($response);
			exit;
		}
		
		// Validate file was uploaded
		if (!isset($_FILES['invoice_json']) || $_FILES['invoice_json']['error'] !== UPLOAD_ERR_OK) {
			$response['message'] = 'file_upload_failed';
			echo json_encode($response);
			exit;
		}
		
		// Validate file type
		$file_info = $_FILES['invoice_json'];
		$file_extension = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
		
		if ($file_extension !== 'json') {
			$response['message'] = 'invalid_file_type';
			echo json_encode($response);
			exit;
		}
		
		// Validate JSON content (optional but recommended for large files)
		$json_content = file_get_contents($file_info['tmp_name']);
		if (json_decode($json_content) === null) {
			$response['message'] = 'invalid_json_content';
			echo json_encode($response);
			exit;
		}
		
		// Permission check for non-admins
		$invoice_permission_id = 5;
		
		if (!$sup_admin) {
			$checkQuery = "SELECT 1 FROM users_permissions WHERE user_id = ? AND permission_id = ? LIMIT 1";
			$checkStmt = $conn->prepare($checkQuery);
			$checkStmt->bind_param("ii", $user_id, $invoice_permission_id);
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
			// Generate unique file name
			$file_name = uniqid('invoice_', true) . '.json';
			
			// Define upload directory
			$upload_dir = '../../assets/invoices/';
			$file_path = $upload_dir . $file_name;
			
			// Create directory if it doesn't exist
			if (!is_dir($upload_dir)) {
				mkdir($upload_dir, 0777, true);
			}
			
			// Move uploaded file
			if (!move_uploaded_file($file_info['tmp_name'], $file_path)) {
				throw new Exception('file_move_failed');
			}
			$file_uploaded = true;
			
			// Insert record into database
			$query = "INSERT INTO invoices (file, date, creator_id) VALUES (?, ?, ?)";
			
			if ($stmt = $conn->prepare($query)) {
				$stmt->bind_param('ssi', $file_name, $invoice_date, $user_id);
				
				if ($stmt->execute()) {
					$invoice_id = $conn->insert_id;
					$conn->commit();
					
					$response['success'] = true;
					$response['message'] = 'upload_success';
					$response['invoice_id'] = $invoice_id;
					$response['file_name'] = $file_name;
					} else {
					throw new Exception('database_error');
				}
				$stmt->close();
				} else {
				throw new Exception('database_error');
			}
			
			} catch (Exception $e) {
			// Rollback transaction on any error
			$conn->rollback();
			
			// Clean up uploaded file if transaction failed
			if ($file_uploaded && file_exists($file_path)) {
				unlink($file_path);
			}
			
			$response['message'] = $e->getMessage();
		}
		} else {
		$response['message'] = 'invalid_request_method';
	}
	
	echo json_encode($response);
?>