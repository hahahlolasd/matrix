(function () {
	// --- Elements ---
	const form         = document.getElementById('profileForm');
	const saveButton   = document.getElementById('saveButton');
	const loadingLine  = document.getElementById('loadingLine');
	const imgTrigger   = document.getElementById('user_edit_picture');
	const imgPreview   = document.getElementById('user_picture');
	
	const inputFull    = document.getElementById('profile_full_name');
	const inputUser    = document.getElementById('profile_username');
	const inputEmail   = document.getElementById('profile_email');
	const inputPass    = document.getElementById('profile_password');
	const inputPass2   = document.getElementById('profile_confirm_password');
	
	// --- Hidden file input for profile image ---
	let selectedFile = null;
	const fileInput = document.createElement('input');
	fileInput.type = 'file';
	fileInput.accept = 'image/jpeg,image/jpg,image/png,image/gif,image/webp';
	fileInput.style.display = 'none';
	document.body.appendChild(fileInput);
	
	if (imgTrigger) {
		imgTrigger.addEventListener('click', function (e) {
			e.preventDefault();
			fileInput.value = '';
			fileInput.click();
		});
	}
	
	fileInput.addEventListener('change', function (e) {
		const file = e.target.files && e.target.files[0];
		selectedFile = file || null;
		if (!selectedFile || !imgPreview) return;
		
		const reader = new FileReader();
		reader.onload = function (ev) {
			imgPreview.src = ev.target.result;
			imgPreview.alt = 'Profile picture';
		};
		reader.readAsDataURL(selectedFile);
	});
	
	// --- Helpers ---
	function startLoading() {
		if (!loadingLine) return;
		loadingLine.style.display = 'block';
		loadingLine.style.width = '0%';
		requestAnimationFrame(() => { loadingLine.style.width = '100%'; });
	}
	function stopLoading() {
		if (!loadingLine) return;
		loadingLine.style.display = 'none';
		loadingLine.style.width = '0%';
	}
	function enableSave()  { if (saveButton) saveButton.disabled = false; }
	function disableSave() { if (saveButton) saveButton.disabled = true; }
	
	function notifySuccess() {
		if (typeof showDynamicIsland === 'function') {
			showDynamicIsland((translations?.profile || 'User') + ' ' + (translations?.update_success || 'updated successfully'), 'success');
		}
	}
	function notifyError(key) {
		const msg = (translations?.[key]) || (translations?.update_error) || 'Update failed';
		if (typeof showDynamicIsland === 'function') {
			showDynamicIsland(msg, 'error');
			} else {
			alert(msg);
		}
	}
	
	// --- Submit handler ---
	if (!form) return;
	
	form.addEventListener('submit', function (e) {
		e.preventDefault();
		
		const pass  = (inputPass?.value || '').trim();
		const pass2 = (inputPass2?.value || '').trim();
		if (pass && pass !== pass2) {
			notifyError('passwords_do_not_match');
			return;
		}
		
		if (saveButton && saveButton.disabled) return;
		disableSave();
		startLoading();
		
		const formData = new FormData(form);
		
		// Trim text fields
		if (inputFull) formData.set('profile_full_name', inputFull.value.trim());
		if (inputUser) formData.set('profile_username', inputUser.value.trim());
		if (inputEmail) formData.set('profile_email', inputEmail.value.trim());
		
		// Remove empty password + confirm
		if (!pass && formData.has('profile_password')) {
			formData.delete('profile_password');
		}
		if (formData.has('profile_confirm_password')) {
			formData.delete('profile_confirm_password');
		}
		
		function upload() {
			fetch('includes/profile/update_user.php', {
				method: 'POST',
				body: formData
			})
			.then(r => r.json().catch(() => ({})))
			.then(res => {
				if (res && res.success) {
					if (inputPass)  inputPass.value = '';
					if (inputPass2) inputPass2.value = '';
					setTimeout(() => {
						notifySuccess();
						setTimeout(() => {
							// Attempt to reload after success.
							// If username/password changed, auth.php will redirect to login anyway.
							location.reload();
						}, 1000);
					}, 300);
					} else {
					stopLoading();
					enableSave();
					notifyError('update_error');
				}
			})
			.catch(() => {
				stopLoading();
				enableSave();
				notifyError('ajax_error');
			});
		}
		
		// Always enforce WEBP via processor when an image is selected
		if (selectedFile) {
			if (typeof processImage === 'function') {
				processImage(selectedFile, 1920, function (processedBlob) {
					if (!processedBlob) {
						stopLoading();
						enableSave();
						notifyError('image_processing_failed');
						return;
					}
					const base = (selectedFile.name.split('.').slice(0, -1).join('.')) || 'avatar';
					const newName = base + '.webp';
					formData.set('profile_image', processedBlob, newName);
					upload();
				});
				} else {
				// Processor must be present for strict WEBP-only flow
				stopLoading();
				enableSave();
				notifyError('image_processing_failed');
			}
			} else {
			upload();
		}
	});
})();
