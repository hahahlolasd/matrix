function processImage(file, maxWidth, callback) {
    // If 'file' is an array, process each file recursively
    if (Array.isArray(file)) {
        const results = [];
        let processedCount = 0;
		
        file.forEach((f, index) => {
            processImage(f, maxWidth, function(blob) {
                results[index] = blob;
                processedCount++;
                if (processedCount === file.length) {
                    callback(results); // return array of blobs
				}
			});
		});
        return; // stop further execution
	}
	
    // Single file processing (existing behavior)
    const reader = new FileReader();
    reader.onload = function (event) {
        const img = new Image();
        img.onload = function () {
            const canvas = document.createElement('canvas');
            let width = img.width;
            let height = img.height;
			
            if (width > maxWidth) {
                const scaleFactor = maxWidth / width;
                width = maxWidth;
                height *= scaleFactor;
			}
			
            canvas.width = width;
            canvas.height = height;
			
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);
			
            canvas.toBlob(function (blob) {
                callback(blob);
			}, 'image/webp', 1);
		};
        img.onerror = function () {
            console.error('Failed to load image for processing');
            callback(null);
		};
        img.src = event.target.result;
	};
    reader.readAsDataURL(file);
}
