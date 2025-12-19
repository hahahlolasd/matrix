<?php if (loadAsset('sidebar_right', $host, $assets)) : ?>
<?php include_once "sidebar_right.php"; ?>
<script src="assets/plugins/sidebar/sidebar.js"></script>
<script src="assets/plugins/sidebar/sidebar-custom.js"></script>
<?php endif; ?>

<!-- Language file (translations) -->
<script>
    var translations = <?php echo json_encode($lang) ?>;
</script>

<!-- Common JS scripts -->
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Rating, Scrollbar, and other common JS scripts -->
<script src='assets/plugins/perfect-scrollbar/perfect-scrollbar.min.js'></script>
<script src='assets/plugins/side-menu/sidemenu.js'></script>
<script src='assets/plugins/mscrollbar/jquery.mCustomScrollbar.concat.min.js'></script>
<script src="assets/js/custom.js"></script>
<script src="assets/js/sticky.js"></script>

<!-- Notifications -->
<script src="assets/plugins/notify/js/notifIt.js"></script>
<script src="assets/plugins/notify/js/notifit-custom.js"></script>
<script src="assets/js/dynamicNotification.js"></script>

<script src="assets/js/process-image.js" defer></script>
<!-- SELECT2 --->
<script src="assets/plugins/select2/js/select2.min.js"></script>
<!-- DROPIFY -->
<link href="assets/plugins/fileuploads/css/fileupload.css?v=<?= $version ?>" rel="stylesheet" type="text/css"/>
<script src="assets/plugins/fileuploads/js/fileupload.js?v=<?= $version ?>"></script>
<script src="assets/plugins/fileuploads/js/file-upload.js?v=<?= $version ?>"></script>

<!-- GLOBAL PLUGINS AND SCRIPTS --->
<script src="assets/js/loading.js?v=<?= $version ?>"></script>

<!-- TEST JS --->
<script src="assets/js/test.js"></script>

<?php if (loadAsset('jqueryui', $host, $assets)) : ?>
<!-- JQUERY UI -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">
<!-- JQUERY UI TOUCH PUNCH -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
<?php endif; ?>

<?php if (loadAsset('datatables', $host, $assets)) : ?>
<!-- DataTables -->
<link href="assets/plugins/datatable/datatables.min.css" rel="stylesheet" />
<link href="assets/plugins/datatable/responsive.dataTables.min.css" rel="stylesheet">
<link href="assets/plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="assets/plugins/datatable/css/jquery.dataTables.min.css" rel="stylesheet">
<script src="assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="assets/plugins/datatable/datatables.min.js"></script>
<script src="assets/plugins/datatable/js/dataTables.bootstrap5.js"></script>
<?php endif; ?>

<?php if (loadAsset('flatpickr', $host, $assets)) : ?>
<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/hu.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/sr.js"></script>
<?php endif; ?>

<?php if (loadAsset('lightgallery', $host, $assets)) : ?>
<!-- LightGallery JS -->
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/lightgallery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/plugins/zoom/lg-zoom.min.js"></script>
<?php endif; ?>

<?php if (loadAsset('warn_on_exit', $host, $assets)) : ?>
<script>
	window.warnOnExit = true;
	window.addEventListener('beforeunload', function (event) {
		if (!window.warnOnExit) return;
		event.preventDefault();
		event.returnValue = '';
	});
	document.querySelector('form')?.addEventListener('submit', function () {
		window.warnOnExit = false;
	});
</script>
<?php endif; ?>

<?php if (loadAsset('flatpickr_init', $host, $assets)) : ?>
<script>
	// Function to get language from cookie
	function getLanguageFromCookie() {
		const match = document.cookie.match(/(^|;)\s*lang=([^;]+)/);
		return match ? match[2] : 'hu'; // Default to Hungarian if not found
	}
	
	// Get current language
	const currentLang = getLanguageFromCookie();
	
	// Configure date format based on language
	let dateFormat = 'Y-m-d'; // Default format
	
	switch(currentLang) {
		case 'hu':
		dateFormat = 'Y. M. d'; // Hungarian format: 2025.12.18
		break;
		case 'sr':
		dateFormat = 'd. M. Y'; // Serbian format: 18.12.2025
		break;
		case 'en':
		dateFormat = 'Y-M-d'; // English/ISO format: 2025-12-18
		break;
	}
	
	// Initialize flatpickr with localized format
	flatpickr('.datepicker', {
		enableTime: false,
		dateFormat: dateFormat,
		
		// Optional: Set locale if you have flatpickr locale files loaded
		// locale: currentLang
	});
</script>
<?php endif; ?>

<?php if (loadAsset('flatpickr_month_select', $host, $assets)) : ?>
<!-- Flatpickr + Month Select -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/hu.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/sr.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
<?php endif; ?>

<?php if (loadAsset('flatpickr_month_select', $host, $assets)) : ?>
<script>
	$(function() {
		function getLangCookie() {
			return document.cookie.split('; ').find(row => row.startsWith('lang='))?.split('=')[1] || 'hu';
		}
		const lang = getLangCookie();
		const localeMap = { en: 'default', hu: 'hu', sr: 'sr' };
		const locale = localeMap[lang] || 'default';
		
		const fp = flatpickr("#flatpickr_month", {
			disableMobile: true,
			locale: locale,
			dateFormat: "Y-m",
			altInput: true,
			altFormat: "Y F",
			plugins: [new monthSelectPlugin({ shorthand: false, dateFormat: "Y-m", theme: "light" })]
		});
		setTimeout(() => {
			if (fp && fp.setDate) fp.setDate(new Date(), true, "Y-m");
		}, 0);
	});
</script>
<?php endif; ?>

<?php if (loadAsset('summernote', $host, $assets)) : ?>
<!-- Summernote Lite -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.9.1/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.9.1/summernote-lite.min.js"></script>
<script>
	$(document).ready(function() {
		$('.summernote').summernote({
			height: 300,
			minHeight: 200,
			maxHeight: 500,
			placeholder: <?= json_encode($lang['start_typing']) ?>,
			toolbar: [
			['style', ['bold', 'italic', 'underline', 'clear']],
			['font', ['strikethrough', 'superscript', 'subscript']],
			['fontsize', ['fontsize']],
			['color', ['color']],
			['height', ['height']],
			['para', ['ul', 'ol', 'paragraph']],
			['insert', ['link']],
			['view', ['fullscreen', 'help']]
			]
		});
	});
</script>
<?php endif; ?>

<?php if (loadAsset('get_profile', $host, $assets)) : ?>
<script src="includes/profile/get_user.js" defer></script>
<?php endif; ?>

<?php if (loadAsset('update_profile', $host, $assets)) : ?>
<script src="includes/profile/update_user.js" defer></script>
<?php endif; ?>

<?php if (loadAsset('get_users', $host, $assets)) : ?>
<script src="includes/users/users_list.js" defer></script>
<?php endif; ?>

<?php if (loadAsset('create_user', $host, $assets)) : ?>
<script src="includes/users/create_user.js" defer></script>
<?php endif; ?>

<?php if (loadAsset('get_user', $host, $assets)) : ?>
<script src="includes/users/get_user.js" defer></script>
<?php endif; ?>

<?php if (loadAsset('edit_user', $host, $assets)) : ?>
<script src="includes/users/edit_user.js" defer></script>
<?php endif; ?>

<?php if (loadAsset('create_invoice', $host, $assets)) : ?>
<script src="includes/invoices/create_invoice.js" defer></script>
<?php endif; ?>

<?php if (loadAsset('get_invoices', $host, $assets)) : ?>
<script src="includes/invoices/get_invoices.js" defer></script>
<?php endif; ?>

</body>

<div id="loadingLine" class="progress position-fixed top-0 start-0 w-100">
    <div class="progress-bar" role="progressbar" style="width:0%; background-color: green;"></div>
</div>

</html>