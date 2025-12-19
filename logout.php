<?php
	// Cookies to keep
	$preserve = ['lang', 'sidebar_collapsed'];
	
	foreach ($_COOKIE as $key => $value) {
		if (in_array($key, $preserve, true)) continue;
		setcookie($key, '', time() - 3600, '/'); // expire
	}
	
	// Redirect the user to login page
	header("Location: login");
	exit;
