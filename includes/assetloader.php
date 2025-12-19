<?php
	
	// ASSET LOADER
	$assets = [
	
	//DATATABLES
	'datatables'				=> ['invoices'],
	
	//WARN ON EXIT
	'warn_on_exit'				=> [''],
	
	//FLATPICKR
	'flatpickr'					=> ['invoices'],
	
	//FLATPICKR
	'flatpickr_month_select'	=> [''],
	
	//LIGHTGALLERY
	'lightgallery'				=> [''],
	
	//SUMMERNOTE
	'summernote'				=> [''],
	
	//JQUERY UI
	'jqueryui'					=> [''],
	
	//SWIPER
	'swiper'					=> [''],
	
	//BREADCRUMB TOOLS
	'breadcrumbTools'			=> ['users', 'invoices'],
	
	//SIDEBAR RIGHT
	'sidebar_right'				=> ['users', 'invoices'],
	
	//AJAX
	'get_profile'				=> ['profile'],
	'update_profile'			=> ['profile'],
	
	//USERS
	'get_users'					=> ['users'],
	'create_user_form'			=> ['users'],
	'create_user'				=> ['users'],
	'get_user'					=> ['edit_user'],
	'edit_user'					=> ['edit_user'],
	
	//INVOICES
	'create_invoice_form'		=> ['invoices'],
	'create_invoice'			=> ['invoices'],
	'get_invoices'				=> ['invoices'],
	'invoice_modal'				=> ['invoices'],
	
	];
	
	// LOADER
	function loadAsset($type, $host, $assets) {
		return in_array($host, $assets[$type] ?? []);
	}	