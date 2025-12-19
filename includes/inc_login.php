<?php
    session_start();
    
    include_once "dbconnect.php";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handleLogin($conn);
	}
    
	function handleLogin($conn) {
		$username = trim($_POST['username'] ?? '');
		$password = $_POST['password'] ?? '';
		
		if (empty($username) || empty($password)) {
			redirectToLogin("error=missing_fields");
		}
		
		$user = getUserByUsername($conn, $username);
		
		if ($user && password_verify($password, $user['password'])) { // Corrected this line
			$sessionId = generateSessionId();
			updateSessionAndLogin($conn, $user['id'], $sessionId);
			setLoginCookies($user, $sessionId);
			session_regenerate_id(true); // Prevent session fixation by regenerating session ID
			header("Location: ../index");
			exit();
		} 
		else {
			redirectToLogin("error=invalid_credentials");
		}
	}	
	
	function getUserByUsername($conn, $username) {
		$sql = $conn->prepare("SELECT id, username, password, name FROM users WHERE username = ?");
		$sql->bind_param("s", $username);
		$sql->execute();
		$sql->bind_result($id, $username, $db_password_hash, $name);
		
		if ($sql->fetch()) {
			$sql->close();
			return [
            'id' => $id,
            'username' => $username,
            'password' => $db_password_hash,
            'name' => $name,
			];
		}
		
		$sql->close();
		return null;
	}	
    
    function updateSessionAndLogin($conn, $userId, $sessionId) {
        $sql = $conn->prepare("UPDATE users SET session_id = ?, last_login = NOW() WHERE id = ?");
        $sql->bind_param("si", $sessionId, $userId);
        $sql->execute();
        $sql->close();
	}
    
    function generateSessionId() {
        return bin2hex(random_bytes(16)); // Generates a secure 32-character session ID
	}
    
	function setLoginCookies($user, $sessionId) {
		$expiry = time() + (86400 * 30); // 30 days
		
		$userId = $user['id'] ?? '';
		$username = $user['username'] ?? '';
		$name = $user['name'] ?? '';
		
		$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'; 
		$httpOnly = false; // keep as you had for local dev
		
		setcookie("user_id", $userId, $expiry, "/", "", $secure, $httpOnly);  
		setcookie("username", $username, $expiry, "/", "", $secure, $httpOnly);
		setcookie("name", $name, $expiry, "/", "", $secure, $httpOnly);
		setcookie("session_id", $sessionId, $expiry, "/", "", $secure, $httpOnly);
		
		// preserve language cookie if already set, otherwise default to 'hu'
		$lang = $_COOKIE['lang'] ?? 'hu';
		setcookie("lang", $lang, $expiry, "/", "", $secure, $httpOnly);
	}
    
    function redirectToLogin($error) {
        header("Location: ../login?$error");
        exit();
	}
?>
