function getCookie(name) {
	const value = `; ${document.cookie}`;
	const parts = value.split(`; ${name}=`);
	return parts.length === 2 ? parts.pop().split(';').shift() : null;
}

function setLang(lang) {
	const currentLang = getCookie('lang');
	console.log(`Current lang: ${currentLang}, New lang: ${lang}`);
	
	if (currentLang === lang) {
		console.log(`Language already set to ${lang}`);
		return false;
	}
	
	// Set the cookie for 30 days
	document.cookie = `lang=${lang}; path=/; max-age=${30 * 24 * 60 * 60}; SameSite=Lax`;
	location.reload();
}