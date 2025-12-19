<?php
	// includes/profile/update_user.php
	include_once '../dbconnect.php';
	include_once '../auth.php'; // sets $user_id or redirects
	
	header('Content-Type: application/json; charset=utf-8');
	
	// Auth guard
	if (!isset($user_id) || !is_numeric($user_id)) {
		echo json_encode(['success' => false, 'message' => 'Unauthorized']);
		exit;
	}
	
	// Inputs
	$full_name = trim($_POST['profile_full_name'] ?? '');
	$username  = trim($_POST['profile_username'] ?? '');
	$email     = trim($_POST['profile_email'] ?? '');
	$password  = $_POST['profile_password'] ?? '';
	$file      = $_FILES['profile_image'] ?? null;
	
	// Validation
	if ($full_name === '' || $username === '' || $email === '') {
		echo json_encode(['success' => false, 'message' => 'Missing required fields']);
		exit;
	}
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		echo json_encode(['success' => false, 'message' => 'Invalid email']);
		exit;
	}
	
	// Get current username + image
	$currentUsername = null;
	$currentImage    = null;
	$stmt = $conn->prepare("SELECT username, image FROM users WHERE id = ?");
	$stmt->bind_param("i", $user_id);
	$stmt->execute();
	$stmt->bind_result($currentUsername, $currentImage);
	$stmt->fetch();
	$stmt->close();
	
	// Handle profile image (strict WEBP only)
	$newImageName = null;
	if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime  = finfo_file($finfo, $file['tmp_name']);
		finfo_close($finfo);
		
		if ($mime !== 'image/webp') {
			echo json_encode(['success' => false, 'message' => 'Only WEBP images are allowed']);
			exit;
		}
		
		$newImageName = 'u' . $user_id . '_' . time() . '.webp';
		
		$publicDir = '../../../assets/images/users/';
		if (!is_dir($publicDir)) {
			@mkdir($publicDir, 0775, true);
		}
		if (!move_uploaded_file($file['tmp_name'], $publicDir . $newImageName)) {
			echo json_encode(['success' => false, 'message' => 'Image upload failed']);
			exit;
		}
		
		// Delete old image if exists and not the default placeholder
		if (!empty($currentImage) && $currentImage !== 'unknown.webp' && is_file($publicDir . $currentImage)) {
			@unlink($publicDir . $currentImage);
		}
	}
	
	// Detect if username changed (case-insensitive compare to avoid trivial differences)
	$usernameChanged = (isset($currentUsername) && strcasecmp($username, (string)$currentUsername) !== 0);
	
	// Build UPDATE
	$fields = [
	'name'     => $full_name,
	'username' => $username,
	'email'    => $email,
	];
	$types  = 'sss';
	$values = [$full_name, $username, $email];
	
	// Optional password
	if ($password !== '') {
		$hash = password_hash($password, PASSWORD_DEFAULT);
		$fields['password'] = $hash;
		$types  .= 's';
		$values[] = $hash;
	}
	
	// Optional image
	if ($newImageName) {
		$fields['image'] = $newImageName;
		$types  .= 's';
		$values[] = $newImageName;
	}
	
	// Compose SQL
	$setParts = [];
	foreach ($fields as $col => $_) {
		$setParts[] = "$col = ?";
	}
	$sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = ?";
	
	$types .= 'i';
	$values[] = $user_id;
	
	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		echo json_encode(['success' => false, 'message' => 'Prepare failed']);
		exit;
	}
	$stmt->bind_param($types, ...$values);
	$ok = $stmt->execute();
	$stmt->close();
	
	// Invalidate session if password changed OR username changed
	if ($ok && ($password !== '' || $usernameChanged)) {
		$clear = $conn->prepare("UPDATE users SET session_id = NULL WHERE id = ?");
		$clear->bind_param("i", $user_id);
		$clear->execute();
		$clear->close();
	}
	
	echo json_encode(['success' => $ok ? true : false, 'message' => $ok ? 'OK' : 'Update failed']);
