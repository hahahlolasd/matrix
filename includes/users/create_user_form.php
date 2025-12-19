<div id="formWrapper">
	<form id="createUser" method="POST" enctype="multipart/form-data">
		
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
					<label for="password"><?= $lang['password'] ?> <span class="required">*</span></label>
					<input type="password" id="password" name="password" placeholder="<?= $lang['password'] ?>" class="form-control" required />
				</div>
			</div>
			
			<div class="col-12 col-md-6 col-lg-4">
				<div class="form-group">
					<label for="confirm_password"><?= $lang['confirm_password'] ?> <span class="required">*</span></label>
					<input type="password" id="confirm_password" name="confirm_password" placeholder="<?= $lang['confirm_password'] ?>" class="form-control" required />
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
					<input name="profile_image" id="profile_image" type="file" class="dropify" data-height="100" accept=".jpg, .jpeg, .png, .webp" />
				</div>
			</div>
			
			<div class="col-12 text-center mt-3">
				<button id="saveButton" class="btn btn-primary waves-effect waves-light w-md" type="submit">
					<i class="bi bi-floppy"></i> <?= $lang['save'] ?> <i class="bi bi-arrow-right"></i> <?= $lang['permissions'] ?> <i class="bi bi-shield-lock"></i>
				</button>
			</div>
			
		</div>
		
	</form>
</div>