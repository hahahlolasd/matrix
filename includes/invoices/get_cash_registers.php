<?php
	// includes/invoices/get_cash_registers.php
	
	include_once '../dbconnect.php';
	include_once '../auth.php';
	
	header('Content-Type: application/json');
	
	try {
		// Query to get all cash registers from the table
		$sql = "SELECT id, name, code 
		FROM cash_registers 
		ORDER BY name ASC";
		
		$result = $conn->query($sql);
		
		$cashRegisters = [];
		if ($result && $result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$cashRegisters[] = [
                'id' => $row['code'],  // Use code as ID for filtering
                'text' => $row['name'] . ' (' . $row['code'] . ')',
                'name' => $row['name'],
                'code' => $row['code']
				];
			}
		}
		
		echo json_encode([
        'success' => true,
        'cash_registers' => $cashRegisters
		]);
		
		} catch (Exception $e) {
		http_response_code(400);
		echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
		]);
	}
?>