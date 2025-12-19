<?php
	header('Content-Type: application/json; charset=utf-8');
	
	try {
		$filename = $_GET['filename'] ?? null;
		
		if (!$filename) {
			throw new Exception('Filename is required');
		}
		
		// Check if file exists
		$safeFilename = basename($filename);
		$jsonFilePath = "../../assets/invoices/" . $safeFilename;
		
		if (!file_exists($jsonFilePath)) {
			throw new Exception('Invoice file not found: ' . $safeFilename);
		}
		
		// Read and parse the JSON file
		$content = file_get_contents($jsonFilePath);
		$allInvoices = json_decode($content, true);
		
		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new Exception('Failed to parse JSON file: ' . json_last_error_msg());
		}
		
		// Collect all items from all invoices
		$allItems = [];
		$itemCount = 0;
		$dailyTotal = 0;
		
		foreach ($allInvoices as $invoice) {
			if (isset($invoice['Items']) && is_array($invoice['Items'])) {
				foreach ($invoice['Items'] as $item) {
					// Extract only the required fields
					$simplifiedItem = [
                    'Name' => $item['Name'] ?? 'Unknown',
                    'Quantity' => $item['Quantity'] ?? 0,
                    'UnitPrice' => $item['UnitPrice'] ?? 0,
                    'TotalAmount' => $item['TotalAmount'] ?? ($item['UnitPrice'] * $item['Quantity'] ?? 0)
					];
					
					// Calculate total amount if not provided
					if (!isset($simplifiedItem['TotalAmount']) || $simplifiedItem['TotalAmount'] == 0) {
						$simplifiedItem['TotalAmount'] = $simplifiedItem['UnitPrice'] * $simplifiedItem['Quantity'];
					}
					
					$allItems[] = $simplifiedItem;
					$itemCount++;
					$dailyTotal += $simplifiedItem['TotalAmount'];
				}
			}
		}
		
		// Prepare simplified response
		$responseData = [
        'filename' => $filename,
        'date' => extractDateFromFilename($filename),
        'item_count' => $itemCount,
        'daily_total' => $dailyTotal,
        'invoice_count' => count($allInvoices),
        'items' => $allItems  // Only contains: Name, Quantity, UnitPrice, TotalAmount
		];
		
		echo json_encode([
        'success' => true,
        'data' => $responseData
		], JSON_UNESCAPED_UNICODE);
		
		} catch (Exception $e) {
		http_response_code(400);
		echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
		], JSON_UNESCAPED_UNICODE);
	}
	
	// Helper function to extract date from filename
	function extractDateFromFilename($filename) {
		// Extract date from filename like "invoices_2025-12-18.json"
		preg_match('/(\d{4}-\d{2}-\d{2})/', $filename, $matches);
		return $matches[1] ?? date('Y-m-d');
	}	