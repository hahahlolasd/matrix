<?php
	
	$pageMap = [
	
	// LOGIN // BELÉPÉS
	
	'login' => [
	'name' => $lang['login'],
	'category' => $lang['login'],
	],
	
	// HOME PAGE // KEZDŐLAP // FŐ OLDAL
	
	'index' => [
	'name' => $lang['dashboard'],
	'category' => $lang['main_section'],
	],
	
	'matrix' => [
	'name' => $lang['dashboard'],
	'category' => $lang['main_section'],
	],
	
	'' => [
	'name' => $lang['dashboard'],
	'category' => $lang['main_section'],
	],
	
	// PROFILE // PROFIL
	
	'profile' => [
	'name' => $lang['profile'],
	'category' => $lang['main_section'],
	],
	
	// USERS // FELHASZNÁLÓK
	
	'users' => [
	'name' => $lang['list_users'],
	'category' => $lang['users'],
	'create_perm' => 1,
	'view_perm' => 2,
	'canFilterName' => true,
	],
	
	'edit_user' => [
	'name' => $lang['edit_user'],
	'category' => $lang['users'],
	'view_perm' => 3,
	],
	
	// INVOICES // SZÁMLÁK
	
	'invoices' => [
	'name' => $lang['invoices'],
	'category' => $lang['invoices'],
	'create_perm' => 5,
	'view_perm' => 6,
	'canFilterDate' => true,
	],
	
	// END
	];
	
?>