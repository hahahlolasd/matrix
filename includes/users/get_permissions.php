<?php
	
	include_once '../dbconnect.php';
	include_once '../auth.php';
	
	header('Content-Type: application/json');
	
	$response = ['success' => false, 'permissions' => []];
	
	// Determine current language
	$language = 'hu'; // default
	if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], ['hu', 'sr'])) {
		$language = $_COOKIE['lang'];
	}
	
	$column_name = "name_" . $language;
	
	$query = "SELECT id, `$column_name` AS name, category FROM permissions ORDER BY id, category";
	
	if ($result = $conn->query($query)) {
		while ($row = $result->fetch_assoc()) {
			$response['permissions'][] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'category' => $row['category'] 
			];
		}
		$response['success'] = true;
		$result->free();
		} else {
		$response['message'] = 'Failed to fetch permissions.';
	}
	
	echo json_encode($response);
