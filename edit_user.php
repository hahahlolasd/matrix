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
				
				<form id="userForm">
					<div class="row row-sm">
						
						<!-- User Info Card -->
						<div class="col-12 col-lg-4">
							<div class="card">
								<div class="card-body">
									
									<h5><?= $lang['account_settings'] ?></h5>
									
									<div class="row">
										
										<div class="col-12 col-md-6 col-lg-4">
											<div class="form-group">
												<label for="full_name"><?= $lang['full_name'] ?> <span class="required">*</span></label>
												<input type="text" id="full_name" name="full_name" placeholder="<?= $lang['full_name'] ?>" class="form-control" required />
											</div>
										</div>
										
										<div class="col-12 col-md-6 col-lg-4">
											<div class="form-group">
												<label for="username"><?= $lang['username'] ?> <span class="required">*</span></label>
												<input type="text" id="username" name="username" placeholder="<?= $lang['username'] ?>" class="form-control" required />
											</div>
										</div>
										
										<div class="col-12 col-md-6 col-lg-4">
											<div class="form-group">
												<label for="email"><?= $lang['email'] ?></label>
												<input type="text" id="email" name="email" placeholder="<?= $lang['email'] ?>" class="form-control" />
											</div>
										</div>
										
										<div class="col-12 col-md-6 col-lg-4">
											<div class="form-group">
												<label for="password"><?= $lang['password'] ?></label>
												<input type="password" id="password" name="password" placeholder="<?= $lang['password'] ?>" class="form-control" />
											</div>
										</div>
										
										<div class="col-12 col-md-6 col-lg-4">
											<div class="form-group">
												<label for="confirm_password"><?= $lang['confirm_password'] ?></label>
												<input type="password" id="confirm_password" name="confirm_password" placeholder="<?= $lang['confirm_password'] ?>" class="form-control" />
											</div>
										</div>
										
										<div class="col-12 col-md-6 col-lg-4">
											<div class="form-group">
												<label for="active"><?= $lang['active'] ?>? <span class="required">*</span></label>
												<select id="active" name="active" class="form-control select2" required>
													<option value="0"><?= $lang['no'] ?></option>
													<option value="1"><?= $lang['yes'] ?></option>
												</select>
											</div>
										</div>
										
										<div class="col-12">
											<div class="form-group">
												<label for="profile_image"><?= $lang['image'] ?> </label>
												
												<div id="profile_image_placeholder"></div>
												
												<input name="profile_image" id="profile_image" type="file" class="dropify" data-height="100" accept=".jpg, .jpeg, .png, .webp" />
											</div>
										</div>
										
									</div>
									
								</div>
							</div>
						</div>
						
						<!-- Permissions Card -->
						<div class="col-12 col-lg-8">
							<div class="card">
								<div class="card-body">
									<h5><?= $lang['permissions'] ?></h5>
									<div id="permissions" class="row">
										<!-- Checkboxes via JS -->
									</div>
								</div>
							</div>
						</div>
						
						<!-- Submit Button -->
						<div class="col-12 col-lg-12 text-center mt-3 mb-5">
							<button class="btn btn-primary waves-effect waves-light w-md" id="saveButton" type="submit">
								<i class="bi bi-floppy"></i> <?= $lang['save'] ?>
							</button>
						</div>
						
					</div>
				</form>
				
				<!-- PAGE SPECIFIC CONTENT END -->
				
			</div>
			<!-- /Container -->
			
		</div>
		<!-- /main-content -->
		
		<?php include './includes/footer.php'; ?>
		
	</div>
	<!-- End Page -->
	
	<!-- Back-to-top -->
	<a href="#top" id="back-to-top"><i class="bi bi-chevron-double-up"></i></a>
	
<?php include './includes/scripts.php'; ?>	