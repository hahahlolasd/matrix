$(function () {
	function getCookie(name) {
		var m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([$?*|{}\]\\^])/g, '\\$1') + '=([^;]*)'));
		return m ? decodeURIComponent(m[1]) : '';
	}
	function formatDate(isoStr, lang) {
		try {
			return new Intl.DateTimeFormat(lang || 'hu', {
				year: 'numeric', month: 'long', day: 'numeric'
			}).format(new Date(isoStr));
		} catch (_e) { return isoStr || ''; }
	}
	
	// Cache selectors
	var $name    = $('#user_name');
	var $title   = $('#user_title');
	var $created = $('#user_created');
	var $picture = $('#user_picture');
	
	var $full    = $('#profile_full_name');
	var $usern   = $('#profile_username');
	var $email   = $('#profile_email');
	
	// Fetch current user (server derives user_id from auth.php)
	$.ajax({
		url: 'includes/profile/get_user.php',
		type: 'POST',
		dataType: 'json',
		cache: false
		}).done(function (res) {
		if (!res || !res.success || !res.data) {
			console.error('Failed to load user:', res && res.message);
			return;
		}
		var d = res.data;
		
		// Header block
		$name.text(d.name || '');
		$title.text(d.sup_admin == "1" ? translations.super_admin : translations.administrator);
		
		if (d.created_at) {
			var lang = getCookie('lang') || 'hu';
			$created.text(formatDate(d.created_at, lang));
		}
		
		if (d.image) {
			$picture.attr('src', 'assets/img/users/' + d.image).attr('alt', 'Profile picture');
		}
		
		// Form fields
		$full.val(d.name || '');
		$usern.val(d.username || '');
		$email.val(d.email || '');
		}).fail(function (xhr, status, err) {
		console.error('User fetch error:', status, err);
	});
});
