<?php
	
	// if ($_SERVER['SERVER_NAME'] === 'localhost') {
	// $dbservername = "localhost";
	// $dbusername = "root";
	// $dbname = "hsnclients_agro";
	// $dbpassword = "";
	// } else {
	$dbservername = "209.42.192.216";
	$dbusername = "tox13_matrix";
	$dbname = "tox13_matrix";
	$dbpassword = "_xCqyhudHIwp";
	// }
	$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
	
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	
	// Set the character set for the connection
	$conn->set_charset("utf8mb4");
	
?>