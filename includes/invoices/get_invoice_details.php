<?php
	header('Content-Type: application/json; charset=utf-8');
	
	// Include your existing database connection
	require_once('../dbconnect.php'); // Adjust path if needed
	
	try {
		$filename = $_GET['filename'] ?? null;
		$cashRegister = $_GET['cash_register'] ?? null; // NEW: Get cash register filter
		
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
		
		// Fetch alternative names mapping from database
		$sql = "SELECT name, alt_name FROM item_alternatives";
		$result = $conn->query($sql);
		
		$alternatives = [];
		if ($result && $result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$alternatives[$row['alt_name']] = $row['name'];
			}
		}
		
		// Function to normalize item name using alternatives table
		function normalizeItemName($itemName, $alternatives) {
			$trimmedName = trim($itemName);
			
			// Check for exact match first (case-insensitive)
			foreach ($alternatives as $alt => $standard) {
				if (strcasecmp(trim($alt), $trimmedName) === 0) {
					return $standard;
				}
			}
			
			return $trimmedName;
		}
		
		// NEW: Extract unique cash registers from the file
		$cashRegistersInFile = [];
		$invoiceCountByRegister = [];
		$itemCountByRegister = [];
		
		// Aggregate items by normalized name
		$aggregatedItems = [];
		$itemCount = 0;  // This should be SUM of quantities, not count of line items
		$dailyTotal = 0;
		$filteredInvoiceCount = 0;
		
		// Track name mappings for debugging/transparency
		$nameMappings = [];
		
		foreach ($allInvoices as $invoice) {
			// Extract cash register from invoice
			$invoiceCashRegister = $invoice['RequestedBy'] ?? 'Unknown';
			
			// Track cash register for the dropdown
			if (!in_array($invoiceCashRegister, $cashRegistersInFile)) {
				$cashRegistersInFile[] = $invoiceCashRegister;
			}
			
			// NEW: Skip invoices that don't match the selected cash register
			if ($cashRegister && $invoiceCashRegister !== $cashRegister) {
				continue;
			}
			
			$filteredInvoiceCount++; // Count invoices after cash register filter
			
			if (isset($invoice['Items']) && is_array($invoice['Items'])) {
				// Track counts by register
				if (!isset($invoiceCountByRegister[$invoiceCashRegister])) {
					$invoiceCountByRegister[$invoiceCashRegister] = 0;
					$itemCountByRegister[$invoiceCashRegister] = 0;
				}
				$invoiceCountByRegister[$invoiceCashRegister]++;
				
				foreach ($invoice['Items'] as $item) {
					$originalName = $item['Name'] ?? 'Unknown';
					$quantity = $item['Quantity'] ?? 0;
					$unitPrice = $item['UnitPrice'] ?? 0;
					
					// Calculate total amount
					$totalAmount = $item['TotalAmount'] ?? 0;
					if ($totalAmount == 0) {
						$totalAmount = $unitPrice * $quantity;
					}
					
					// Normalize the item name using alternatives table
					$normalizedName = normalizeItemName($originalName, $alternatives);
					
					// Track name changes for reference
					if ($originalName !== $normalizedName && !isset($nameMappings[$originalName])) {
						$nameMappings[$originalName] = $normalizedName;
					}
					
					// Aggregate by normalized item name
					if (!isset($aggregatedItems[$normalizedName])) {
						$aggregatedItems[$normalizedName] = [
						'Name' => $normalizedName,
						'OriginalNames' => [],
						'Quantity' => 0,
						'UnitPrice' => $unitPrice,
						'TotalAmount' => 0
						];
					}
					
					// Add to aggregated values
					$aggregatedItems[$normalizedName]['Quantity'] += $quantity;
					$aggregatedItems[$normalizedName]['TotalAmount'] += $totalAmount;
					
					// Track all original names that map to this normalized name
					if (!in_array($originalName, $aggregatedItems[$normalizedName]['OriginalNames'])) {
						$aggregatedItems[$normalizedName]['OriginalNames'][] = $originalName;
					}
					
					// FIXED: Add the QUANTITY, not just count the line item
					$itemCount += $quantity;  // CHANGED FROM $itemCount++
					$dailyTotal += $totalAmount;
					$itemCountByRegister[$invoiceCashRegister] += $quantity;
				}
			}
		}
		
		// NEW: Get cash register names from database for better display
		$cashRegistersWithNames = [];
		if (!empty($cashRegistersInFile)) {
			// Create placeholders for the IN clause
			$placeholders = str_repeat('?,', count($cashRegistersInFile) - 1) . '?';
			$sql = "SELECT code, name FROM cash_registers WHERE code IN ($placeholders)";
			$stmt = $conn->prepare($sql);
			
			// Bind parameters
			$types = str_repeat('s', count($cashRegistersInFile));
			$stmt->bind_param($types, ...$cashRegistersInFile);
			$stmt->execute();
			$result = $stmt->get_result();
			
			$cashRegisterNames = [];
			while ($row = $result->fetch_assoc()) {
				$cashRegisterNames[$row['code']] = $row['name'];
			}
			
			// Create array with names
			foreach ($cashRegistersInFile as $code) {
				$name = $cashRegisterNames[$code] ?? $code;
				$invoiceCount = $invoiceCountByRegister[$code] ?? 0;
				$itemCount = $itemCountByRegister[$code] ?? 0;
				
				$cashRegistersWithNames[] = [
                'code' => $code,
                'name' => $name,
                'display_text' => $name . ' (' . $code . ') - ' . $invoiceCount . ' invoices',
                'invoice_count' => $invoiceCount,
                'item_count' => $itemCount
				];
			}
			
			// Sort by name
			usort($cashRegistersWithNames, function($a, $b) {
				return strcmp($a['name'], $b['name']);
			});
		}
		
		// Convert associative array to indexed array
		$allItems = array_values($aggregatedItems);
		
		// Prepare simplified response
		$responseData = [
        'filename' => $filename,
        'date' => extractDateFromFilename($filename),
        'item_count' => $itemCount,
        'aggregated_item_count' => count($aggregatedItems),
        'daily_total' => $dailyTotal,
        'invoice_count' => count($allInvoices),
        'filtered_invoice_count' => $filteredInvoiceCount, // NEW
        'selected_cash_register' => $cashRegister, // NEW
        'cash_registers' => $cashRegistersWithNames, // NEW
        'name_mappings' => $nameMappings,
        'items' => $allItems
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
		} finally {
		// Close database connection if you want to be explicit
		if (isset($conn)) {
			$conn->close();
		}
	}
	
	// Helper function to extract date from filename
	function extractDateFromFilename($filename) {
		// Extract date from filename like "invoices_2025-12-18.json"
		preg_match('/(\d{4}-\d{2}-\d{2})/', $filename, $matches);
		return $matches[1] ?? date('Y-m-d');
	}				