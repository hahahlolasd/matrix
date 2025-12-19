$(document).ready(function () {
    $('#userForm').submit(function (e) {
        e.preventDefault();
        
        let user_id = localStorage.getItem('selectedEditUserId');
        let name = $('#full_name').val();
        let email = $('#email').val();
        let username = $('#username').val();
        let password = $('#password').val();
        let confirmPassword = $('#confirm_password').val();
        let active = $('#active').val();
        
        if (password !== '' && password !== confirmPassword) {
            showDynamicIsland(translations.passwords_do_not_match, 'error');
            return;
		}
        
        let formData = new FormData();
        formData.append('user_id', user_id);
        formData.append('name', name);
        formData.append('email', email);
        formData.append('username', username);
        formData.append('active', active);
        
        if (password !== '') {
            formData.append('password', password);
		}
        
        // Collect checked permissions
        $('input[name="permissions[]"]:checked').each(function () {
            formData.append('permissions[]', $(this).val());
		});
        
        // Process and add profile image if exists
        const profileImageInput = $('#profile_image')[0];
        if (profileImageInput.files.length > 0) {
            const profileImageFile = profileImageInput.files[0];
            
            // Process image before submission
            processImage(profileImageFile, 800, function(processedBlob) {
                if (processedBlob) {
                    formData.append('profile_image', processedBlob, 'profile_image.webp');
				}
                submitFormData(formData);
			});
			} else {
            // No image to process, submit directly
            submitFormData(formData);
		}
	});
});

function submitFormData(formData) {
    LoadingLine.start({ disable: '#saveButton' });
    
    $.ajax({
        url: 'includes/users/edit_user.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            LoadingLine.finish();
            showDynamicIsland(translations.user + " " + translations.update_success, "success");
            localStorage.removeItem('selectedEditUserId');
            
            setTimeout(function () {
                window.location.href = 'users';
			}, 3000);
		},
        error: function () {
            LoadingLine.fail();
            showDynamicIsland(translations.user + " " + translations.update_error, "error");
            $('#saveButton').prop('disabled', false);
		}
	});
}

// Function to get cookie value
function getCookie(name) {
    let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? match[2] : null;
}