<?php 
	$host = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
	$host = rtrim($host, '.php');
	$root = $_SERVER['DOCUMENT_ROOT'];
	$is_mobile = strpos($_SERVER['HTTP_USER_AGENT'] ?? '', 'mobi') !== false;
	$appName = "Matrix Laser";
	$appNameShort = "Matrix";
	
	// Desktop respects cookie; mobile always starts closed
	$collapsed = !$is_mobile && (($_COOKIE['sidebar_collapsed'] ?? '0') === '1');	
	
	include_once 'dbconnect.php';
	
	$version = "1";
	
	// Determine language from cookie
	$lang = $_COOKIE['lang'] ?? 'hu';
	$langFile = "./lang/$lang.php";
	include_once $langFile;
	
	include_once 'pagemap.php';
	
	$pageName = '';
	
	if (isset($pageMap[$host])) {
		if (is_array($pageMap[$host])) {
			$pageName = $pageMap[$host]['name'];
			$pageCategory = $pageMap[$host]['category'];
			$addNewText = $pageMap[$host]['add_text'] ?? $lang['add'];
		}
	}
	
	$pageTitle = ($pageName ? "$pageName :: " : "") . $appName;
?>
