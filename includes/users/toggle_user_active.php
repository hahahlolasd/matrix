<?php
	include_once '../dbconnect.php';
	
	// Check if user_id and active status are provided
	if (isset($_POST['user_id']) && isset($_POST['active'])) {
		$user_id = (int) $_POST['user_id'];
		$active = (int) $_POST['active'];
		
		// Validate active status (should be 0 or 1)
		if ($active !== 0 && $active !== 1) {
			$response = ['success' => false, 'message' => 'Invalid active status'];
			echo json_encode($response);
			exit;
		}
		
		// Initialize response array
		$response = ['success' => false, 'message' => ''];
		
		// Prepare the UPDATE statement to toggle active status
		$stmt = $conn->prepare("UPDATE users SET active = ? WHERE id = ?");
		
		if ($stmt) {
			// Bind the parameters
			$stmt->bind_param("ii", $active, $user_id);
			
			// Execute the statement
			if ($stmt->execute()) {
				// Check if any row was actually updated
				if ($stmt->affected_rows > 0) {
					$response['success'] = true;
					$response['message'] = $active == 1 ? 'User activated successfully' : 'User deactivated successfully';
					} else {
					$response['message'] = 'No user found with the provided ID';
				}
				} else {
				$response['message'] = 'Error: ' . $stmt->error;
			}			
			
			// Close the statement
			$stmt->close();
			} else {
			// If there was an issue preparing the statement
			$response['message'] = 'STMT Error: ' . $conn->error;
		}
		
		// Close the database connection
		$conn->close();
		
		// Return the response in JSON format
		echo json_encode($response);
	} 
	else {
		// If required parameters are not provided
		$response = ['success' => false, 'message' => 'No user ID or active status provided'];
		echo json_encode($response);
	}
?>