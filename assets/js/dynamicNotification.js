function showDynamicIsland(message, type) {
    let island = document.createElement("div");
    island.classList.add("dynamic-island");
	
    // Apply specific class for each type to set the background color
    if (type === "success") {
        island.classList.add("success");
        island.innerHTML = '<span><i class="bi bi-check-circle"></i> ' + message + '</span>';
	} 
	else if (type === "upload") {
        island.classList.add("success");
        island.innerHTML = '<span><i class="bi bi-cloud-arrow-up"></i> ' + message + '</span>';
	} 
	else if (type === "delete") {
        island.classList.add("success");
        island.innerHTML = '<span><i class="bi bi-trash"></i> ' + message + '</span>';
	} 
	else if (type === "error") {
        island.classList.add("error");
        island.innerHTML = '<span><i class="bi bi-bug"></i> ' + message + '</span>';
	} 
	else if (type === "info") {
        island.classList.add("info");
        island.innerHTML = '<span><i class="bi bi-info-circle"></i> ' + message + '</span>';
	}
	else if (type === "warning") {
        island.classList.add("warning");
        island.innerHTML = '<span><i class="bi bi-exclamation-triangle"></i> ' + message + '</span>';
	}
	
    document.body.appendChild(island);
    
    // Trigger the entry animation (active class)
    setTimeout(() => {
        island.classList.add("active");
	}, 10);
    
    // Remove the active class and trigger leaving animation after 3 seconds
    setTimeout(() => {
        island.classList.remove("active");
        
        // Add the leaving class to trigger the exit animation
        island.classList.add("leaving");
        
        // Remove the island after the leaving animation completes
        setTimeout(() => island.remove(), 1000); // 1000ms (1 second) to match the leaving animation duration
	}, 3000);
}
