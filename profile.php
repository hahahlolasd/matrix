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
				
				<div class="row row-sm">
					<div class="col-md-2">
						<div class="card mg-b-20">
							<div class="card-body">
								<div class="main-profile-overview">
									<div class="main-img-user profile-user">
										<img alt="Profile picture" id="user_picture" />
										<a class="bi bi-camera-fill profile-edit" id="user_edit_picture" href="JavaScript:void(0);"></a>
									</div>
									<div>
										<h5 class="main-profile-name" id="user_name"></h5>
										<p class="main-profile-name-text" id="user_title"></p>
										<p class="main-profile-name-text"><span><?= $lang['account_created'] ?>:</span> <span id="user_created"></span></h5>
									</div>
								</div><!-- main-profile-overview -->
							</div>
						</div>
					</div>
					
					<div class="col-md-6">
						<div class="card">
							<div class="card-body">
								
								<h5><?= $lang['account_settings'] ?></h5>
								
								<form id="profileForm">
									<div class="row">
										
										<div class="col-md-4">
											<div class="form-group">
												<label for="profile_full_name"><?= $lang['full_name'] ?> <span class="required">*</span></label>
												<input type="text" id="profile_full_name" name="profile_full_name" class="form-control" required />
											</div>
										</div>
										
										<div class="col-md-4">
											<div class="form-group">
												<label for="profile_username"><?= $lang['username'] ?> <span class="required">*</span></label>
												<input type="text" id="profile_username" name="profile_username" class="form-control" required />
											</div>
										</div>
										
										<div class="col-md-4">
											<div class="form-group">
												<label for="profile_email"><?= $lang['email'] ?> <span class="required">*</span></label>
												<input type="email" id="profile_email" name="profile_email" class="form-control" required />
											</div>
										</div>
										
										
									</div>
									
									<div class="row">
										
										<div class="col-md-6">
											<div class="form-group">
												<label for="profile_password"><?= $lang['new_password'] ?></label>
												<input type="password" id="profile_password" name="profile_password" class="form-control">
											</div>
										</div>
										
										<div class="col-md-6">
											<div class="form-group">
												<label for="profile_confirm_password"><?= $lang['confirm_new_password'] ?></label>
												<input type="password" id="profile_confirm_password" name="profile_confirm_password" class="form-control">
											</div>
										</div>
										
										<div class="col-md-12 text-center mt-3">
											<button class="btn btn-primary waves-effect waves-light w-md" id="saveButton" type="submit"><i class="bi bi-floppy"></i> <?= $lang['save'] ?></button>
										</div>
										
									</div>
								</form>
								
							</div>
						</div>
					</div>
				</div>
				
				<!-- PAGE SPECIFIC CONTENT END -->
				
			</div>
			<!-- /Container -->
			
		</div>
		<!-- /main-content -->
		
		<?php include './includes/footer.php'; ?>
		
	</div>
	<!-- End Page -->
	
	<div id="loadingLine"></div>
	
	<!-- Back-to-top -->
	<a href="#top" id="back-to-top"><i class="bi bi-chevron-double-up"></i></a>
	
<?php include './includes/scripts.php'; ?>	