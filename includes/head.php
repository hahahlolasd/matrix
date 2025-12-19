<?php
	
	include_once "./includes/config.php";
	include_once "./includes/pagemap.php";
	include_once "./includes/auth.php";
	include_once "./includes/assetloader.php";
	
?>

<!DOCTYPE html>
<html lang="<?= $_COOKIE['lang'] ?? 'hu' ?>">
	<head>
		
		<meta charset="UTF-8">
		<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="Description" content="">
		<meta name="Author" content="">
		<meta name="Keywords" content="" />
		
		<!-- Title -->
		<title><?= $pageTitle ?></title>
		
		<!-- Favicon -->
		<link rel="shortcut icon" href="assets/img/favicon/favicon.png">
		
		<!--Internal Notify -->
		<link href="assets/plugins/notify/css/notifIt.css?v=<?= $version ?>" rel="stylesheet" />
		
		<!-- Language Switcher -->
		<script src='assets/js/languageSwitcher.js?v=<?= $version ?>' defer></script>
		
		<!-- JQUERY -->
		<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
		
		<!-- Select2 -->
		<link href='assets/plugins/select2/css/select2.min.css' rel='stylesheet'>
		
		<?php
			
			if (loadAsset('lightgallery', $host, $assets)) {
				echo "
				<!-- LightGallery CSS -->
				<link href='https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/css/lightgallery-bundle.min.css' rel='stylesheet' />
				";
			}
			
			if (loadAsset('swiper', $host, $assets)) {
				echo "
				<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css'>
				<script src='https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js'></script>
				";
			}
		?>
		
		<!-- Bootstrap css -->
		<link href="assets/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">
		
		<!-- Bootstrap icons -->
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
		
		<!-- MATERIAL ICONS -->
		<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
		
		<!-- Right-sidemenu css -->
		<link href="assets/plugins/sidebar/sidebar.css?v=<?= $version ?>" rel="stylesheet">
		
		<!-- Custom Scroll bar-->
		<link href='assets/plugins/mscrollbar/jquery.mCustomScrollbar.css?v=<?= $version ?>' rel='stylesheet' />
		
		<!-- Sidemenu css -->
		<link rel='stylesheet' href='assets/css/sidemenu.css?v=<?= $version ?>'>
		
		<!--- Style css --->
		<link href="assets/css/style.css?v=<?= $version ?>" rel="stylesheet">
		
		<!--- Animations css --->
		<link href="assets/css/animate.css?v=<?= $version ?>" rel="stylesheet">
		
		<!--- Dynamic Notification css --->
		<link href="assets/css/dynamicNotification.css?v=<?= $version ?>" rel="stylesheet">
		
		<!-- TEST CSS --->
		<link href="assets/css/test.css?v=<?= $version ?>" rel="stylesheet">
		
	</head>																		