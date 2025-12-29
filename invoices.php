<?php include './includes/head.php'; ?>

<body class="main-body app sidebar-mini">
	
	<!-- Loader -->
	<div id="global-loader">
		<img src="assets/img/loader.svg" class="loader-img" alt="Loader">
	</div>
	<!-- /Loader -->
	
	<!-- Page -->
	<div class="page">
		
		<?php include './includes/sidebar.php'; ?>
		
		<!-- main-content -->
		<div class="main-content app-content">
			
			<?php include './includes/header.php'; ?>
			
			<!-- container -->
			<div class="container-fluid">
				
				<?php include_once './includes/breadcrumb.php'; ?>
				
				<!-- PAGE SPECIFIC CONTENT -->
				
				<div class="col-12">
					
					<div class="table-responsive table-striped">
						<table id="invoices_table" class="table datatables-table key-buttons text-md-nowrap"></table>
					</div>
					
				</div>
				
				<!-- END PAGE SPECIFIC CONTENT -->
				
			</div>
			<!-- /Container -->
		</div>
		<!-- /main-content -->
		
		<?php include './includes/footer.php'; ?>
		
	</div>
	<!-- End Page -->
	
	<!-- Back-to-top -->
	<a href="#top" id="back-to-top"><i class="bi bi-chevron-double-up"></i></a>
	
	<div id='dailyItemsModalContainer'></div>
	
<?php include './includes/scripts.php'; ?>	