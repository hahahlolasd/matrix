<?php
	include_once "includes/config.php";
	
	if (isset($_COOKIE['session_id'])) {
		$session_id = $_COOKIE['session_id'];
		
		// Query the users table to check if the session ID exists and retrieve the user's ID
		$sql = "SELECT id, session_id FROM users WHERE session_id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $session_id);
		$stmt->execute();
		$result = $stmt->get_result();
		
		// If session ID exists and matches a user
		if ($result->num_rows > 0) {
			$user = $result->fetch_assoc();
			
			// Check if the session ID matches the user's session ID (to prevent session hijacking)
			if ($user['session_id'] === $session_id) {
				// Session is valid, continue with the page logic
				header("Location: index");
				exit();
			}
		}
	}
?>


<!DOCTYPE html>
<html lang="en">
	
	<head>
		
		<meta charset="UTF-8">
		<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		
		<title><?= $lang['login'] ?> :: <?= $appName ?></title>
		
		<!-- Google Fonts -->
		<link href='https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700|Lato:400,100,300,700,900' rel='stylesheet' type='text/css'>
		
		<!-- FLAG ICONS -->
		<link href='https://cdnjs.cloudflare.com/ajax/libs/flag-icons/7.5.0/css/flag-icons.min.css' rel='stylesheet'>
		
		<link rel="stylesheet" href="assets/css/login/animate.css">
		<!-- Custom Stylesheet -->
		<link rel="stylesheet" href="assets/css/login/style.css">
		
		<link rel="shortcut icon" href="assets/img/favicon/favicon.png">
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
	</head>
	
	<body>
		<div class="container">
			<div class="login-box animated fadeInUp">
				<div class="box-header">
					<h2><?= $lang["login"] ?></h2>
				</div>
				
				<div class="lang-switcher-container">
					<i class="fi fi-hu"></i> <i class="fi fi-rs"></i>
				</div>
				
				<form action="includes/inc_login.php" method="post">
					<label for="username"><?= $lang["username"] ?></label>
					<br/>
					<input type="text" name="username" required>
					<br/>
					<label for="password"><?= $lang["password"] ?></label>
					<br/>
					<input type="password" name="password" required>
					<br/>
					<button type="submit"><?= $lang["login"] ?></button>
					<?php
						if (isset($_GET['error'])) {
							
							switch ($_GET['error']) {
								
								case "invalid_credentials":
								echo "<h4 class='text-danger'>{$lang['invalid_credentials']}!</h4>";
								break;
								
								case "missing_fields":
								echo "<h4 class='text-danger'>{$lang['missing_fields']}!</h4>";
								break;
								
							}
							
						}
					?>
				</form>
				
			</div>
		</div>
	</body>
	
	<script>
		$(document).ready(function () {
			$('#logo').addClass('animated fadeInDown');
			$("input:text:visible:first").focus();
		});
		$('#username').focus(function() {
			$('label[for="username"]').addClass('selected');
		});
		$('#username').blur(function() {
			$('label[for="username"]').removeClass('selected');
		});
		$('#password').focus(function() {
			$('label[for="password"]').addClass('selected');
		});
		$('#password').blur(function() {
			$('label[for="password"]').removeClass('selected');
		});
	</script>
	
	<script>
		$(function() {
			$('.fi-hu').on('click', function() {
				document.cookie = "lang=hu; path=/; max-age=" + (60*60*24*365);
				location.reload();
			});
			
			$('.fi-rs').on('click', function() {
				document.cookie = "lang=sr; path=/; max-age=" + (60*60*24*365);
				location.reload();
			});
		});
	</script>	
	
</html>