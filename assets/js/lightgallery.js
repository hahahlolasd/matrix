window.showImageInLightGallery = function(imgSrc) {
    // Remove old instance if exists
    if (window.lgInstance) {
        window.lgInstance.destroy();
	}
	
    // Remove any existing containers
    $('#lightgallery-temp').remove();
	
    // Create temporary container with one image
    const $gallery = $(`
        <div id="lightgallery-temp" style="display:none;">
		<a href="${imgSrc}" data-lg-size="1400-933"></a>
        </div>
	`);
    $('body').append($gallery);
	
    // Initialize LightGallery
    window.lgInstance = lightGallery(document.getElementById('lightgallery-temp'), {
        plugins: [lgZoom],
        zoom: true,
        download: false
	});
	
    // Open the gallery
    window.lgInstance.openGallery();
	
    // Clean up on close
    document.addEventListener('lgAfterClose', () => {
        $('#lightgallery-temp').remove();
	}, { once: true });
};
