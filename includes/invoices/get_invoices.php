<?php
	
	include_once '../dbconnect.php';
	include_once '../auth.php';
	
	header('Content-Type: application/json');
	
	// DataTables parameters
	$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
	$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
	$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
	$search_value = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
	$order_column_index = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
	$order_direction = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc';
	
	// Date filter parameters
	$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
	$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
	
	// Map DataTables columns to database columns (0=id, 1=file, 2=date, 3=creator_name, 4=actions)
	$columns = ['id', 'file', 'date', 'creator_id', null];
	$order_by = isset($columns[$order_column_index]) ? $columns[$order_column_index] : 'id';
	
	// Response array
	$response = [
	'draw' => $draw,
	'recordsTotal' => 0,
	'recordsFiltered' => 0,
	'data' => []
	];
	
	try {
		// Build base query and conditions
		$base_query = "FROM invoices i LEFT JOIN users u ON i.creator_id = u.id";
		$where_conditions = [];
		$query_params = [];
		$param_types = '';
		
		// Add text search condition
		if (!empty($search_value)) {
			$where_conditions[] = "(i.file LIKE ? OR u.username LIKE ?)";
			$search_term = "%$search_value%";
			$query_params[] = $search_term;
			$query_params[] = $search_term;
			$param_types .= 'ss';
		}
		
		// Add date range conditions
		if (!empty($date_from)) {
			$where_conditions[] = "i.date >= ?";
			$query_params[] = $date_from;
			$param_types .= 's';
		}
		
		if (!empty($date_to)) {
			$where_conditions[] = "i.date <= ?";
			$query_params[] = $date_to;
			$param_types .= 's';
		}
		
		// Build WHERE clause
		$where_clause = '';
		if (!empty($where_conditions)) {
			$where_clause = ' WHERE ' . implode(' AND ', $where_conditions);
		}
		
		// Get total records count
		$count_query = "SELECT COUNT(*) as total FROM invoices";
		$count_result = $conn->query($count_query);
		$total_records = $count_result->fetch_assoc()['total'];
		
		// Get filtered count
		$filtered_query = "SELECT COUNT(*) as filtered $base_query $where_clause";
		$filtered_stmt = $conn->prepare($filtered_query);
		
		if (!empty($query_params)) {
			$filtered_stmt->bind_param($param_types, ...$query_params);
		}
		
		$filtered_stmt->execute();
		$filtered_result = $filtered_stmt->get_result();
		$filtered_records = $filtered_result->fetch_assoc()['filtered'];
		
		// Build main data query with ordering and pagination
		$data_query = "SELECT i.*, u.name as creator_name 
		$base_query 
		$where_clause 
		ORDER BY $order_by $order_direction 
		LIMIT ? OFFSET ?";
		
		// Add limit and offset to parameters
		$query_params[] = $length;
		$query_params[] = $start;
		$param_types .= 'ii';
		
		// Prepare and execute main query
		$stmt = $conn->prepare($data_query);
		
		if (!empty($query_params)) {
			$stmt->bind_param($param_types, ...$query_params);
		}
		
		$stmt->execute();
		$result = $stmt->get_result();
		
		$data = [];
		while ($row = $result->fetch_assoc()) {
			$data[] = [
			'id' => $row['id'],
			'file' => $row['file'],
			'date' => $row['date'],
			'creator_name' => $row['creator_name'] ?? 'Unknown'
			];
		}
		
		// Build response
		$response = [
		'draw' => $draw,
		'recordsTotal' => $total_records,
		'recordsFiltered' => $filtered_records,
		'data' => $data
		];
		
		} catch (Exception $e) {
		// Return error response
		$response['error'] = $e->getMessage();
	}
	
	echo json_encode($response);
?>