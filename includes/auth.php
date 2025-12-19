<?php
	
	if (isset($_COOKIE['session_id']) && isset($_COOKIE['user_id'])) {
		$session_id = $_COOKIE['session_id'];
		$user_id = $_COOKIE['user_id'];
		
		$sql = "
		SELECT u.id, u.session_id, u.sup_admin, up.permission_id
		FROM users u
		LEFT JOIN users_permissions up ON u.id = up.user_id
		WHERE u.id = ? AND u.session_id = ?
		";
		
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("is", $user_id, $session_id);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if ($result->num_rows > 0) {
			$permissions = [];
			$sup_admin = 0;
			
			while ($row = $result->fetch_assoc()) {
				if ($row['session_id'] !== $session_id) {
					header("Location: logout");
					exit();
				}
				
				if (!$sup_admin) {
					$sup_admin = (int)$row['sup_admin'];
				}
				
				if (!is_null($row['permission_id'])) {
					$permissions[] = $row['permission_id'];
				}
			}
			} else {
			header("Location: logout");
			exit();
		}
		} else {
		header("Location: logout");
		exit();
	}
	
	if (isset($pageMap, $host) && isset($pageMap[$host])) {
		$page = $pageMap[$host];
		
		if (isset($page['view_perm']) && !$sup_admin) {
			if (!in_array($page['view_perm'], $permissions)) {
				header("Location: index?access_denied");
				exit();
			}
		}
	}
	
	function canCreate($host) {
		global $sup_admin, $permissions, $pageMap;
		
		// Super admins can create everything
		if ($sup_admin) {
			return true;
		}
		
		// Check if page has create permission and user has it
		if (isset($pageMap[$host]) && isset($pageMap[$host]['create_perm'])) {
			return in_array($pageMap[$host]['create_perm'], $permissions);
		}
		
		return false;
	}
	
	function canEdit($host) {
		global $sup_admin, $permissions, $pageMap;
		
		if ($sup_admin) {
			return true;
		}
		
		if (isset($pageMap[$host]) && isset($pageMap[$host]['view_perm'])) {
			return in_array($pageMap[$host]['view_perm'], $permissions);
		}
		
		return false;
	}
	
	function canFilterDate($host) {
		global $pageMap;
		
		if (isset($pageMap[$host]) && isset($pageMap[$host]['canFilterDate'])) {
			return (bool) $pageMap[$host]['canFilterDate'];
		}
		
		return false;
	}
	
	function canFilterName($host) {
		global $pageMap;
		
		if (isset($pageMap[$host]) && isset($pageMap[$host]['canFilterName'])) {
			return (bool) $pageMap[$host]['canFilterName'];
		}
		
		return false;
	}