/**
 * Camera access utility for the attendance system
 */
document.addEventListener('DOMContentLoaded', function() {
    // Elements for check-in camera
    const checkInElements = {
        video: document.getElementById('camera-feed'),
        canvas: document.getElementById('photo-canvas'),
        captureBtn: document.getElementById('capture-btn'),
        retakeBtn: document.getElementById('retake-btn'),
        fileInput: document.getElementById('photo'),
        submitBtn: document.getElementById('submit-btn')
    };

    // Elements for check-out camera
    const checkOutElements = {
        video: document.getElementById('camera-feed-out'),
        canvas: document.getElementById('photo-canvas-out'),
        captureBtn: document.getElementById('capture-btn-out'),
        retakeBtn: document.getElementById('retake-btn-out'),
        fileInput: document.getElementById('photo-out'),
        submitBtn: document.getElementById('submit-btn-out')
    };

    // Initialize camera for check-in if elements exist
    if (checkInElements.video) {
        initCamera(checkInElements);
    }

    // Initialize camera for check-out if elements exist
    if (checkOutElements.video) {
        initCamera(checkOutElements);
    }

    // Initialize geolocation
    initGeolocation();
});

/**
 * Initialize camera functionality
 */
function initCamera(elements) {
    const { video, canvas, captureBtn, retakeBtn, fileInput, submitBtn } = elements;
    let stream;

    // Request camera access with explicit constraints
    const constraints = {
        audio: false,
        video: {
            width: { ideal: 1280 },
            height: { ideal: 720 },
            facingMode: "user"
        }
    };

    // Start camera with explicit error handling
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia(constraints)
            .then(function(mediaStream) {
                stream = mediaStream;
                video.srcObject = mediaStream;
                video.onloadedmetadata = function(e) {
                    video.play();
                };
                console.log("Camera access granted successfully");
            })
            .catch(function(error) {
                console.error('Error accessing camera:', error);

                // Display user-friendly error message based on error type
                let errorMessage = "Unable to access camera. ";

                if (error.name === "NotAllowedError" || error.name === "PermissionDeniedError") {
                    errorMessage += "Camera permission was denied. Please check your browser settings and grant permission.";
                } else if (error.name === "NotFoundError" || error.name === "DevicesNotFoundError") {
                    errorMessage += "No camera device was found on your device.";
                } else if (error.name === "NotReadableError" || error.name === "TrackStartError") {
                    errorMessage += "Your camera might be in use by another application.";
                } else if (error.name === "OverconstrainedError") {
                    errorMessage += "The requested camera constraints cannot be satisfied.";
                } else if (error.name === "TypeError") {
                    errorMessage += "No camera specifications were provided.";
                }

                // Create and display error message on page
                const errorElement = document.createElement('div');
                errorElement.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4';
                errorElement.innerHTML = `<span class="block">${errorMessage}</span>`;

                // Insert error message before the video element
                if (video.parentNode) {
                    video.parentNode.parentNode.insertBefore(errorElement, video.parentNode);
                    video.parentNode.classList.add('hidden');
                }
            });
    } else {
        console.error('getUserMedia is not supported in this browser');
        alert('Camera access is not supported in this browser. Please use a modern browser like Chrome, Firefox, or Edge.');
    }

    // Capture photo
    if (captureBtn) {
        captureBtn.addEventListener('click', function() {
            if (!stream) {
                console.error('No active camera stream available');
                return;
            }

            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert canvas to file
            canvas.toBlob(function(blob) {
                const file = new File([blob], 'photo.jpg', { type: 'image/jpeg' });

                // Use the newer DataTransfer API
                try {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;

                    // Show canvas, hide video
                    video.classList.add('hidden');
                    canvas.classList.remove('hidden');

                    // Show retake button, hide capture button
                    captureBtn.classList.add('hidden');
                    retakeBtn.classList.remove('hidden');

                    // Enable submit button
                    submitBtn.disabled = false;
                } catch (error) {
                    console.error('Error creating file from canvas:', error);
                    alert('Failed to capture photo. Your browser might not support this feature.');
                }
            }, 'image/jpeg', 0.9);
        });
    }

    // Retake photo
    if (retakeBtn) {
        retakeBtn.addEventListener('click', function() {
            // Show video, hide canvas
            video.classList.remove('hidden');
            canvas.classList.add('hidden');

            // Show capture button, hide retake button
            captureBtn.classList.remove('hidden');
            retakeBtn.classList.add('hidden');

            // Disable submit button
            submitBtn.disabled = true;

            // Clear the file input
            fileInput.value = '';
        });
    }
}

/**
 * Initialize geolocation
 */
function initGeolocation() {
    const latitudeInputs = [
        document.getElementById('latitude'),
        document.getElementById('latitude-out')
    ];

    const longitudeInputs = [
        document.getElementById('longitude'),
        document.getElementById('longitude-out')
    ];

    if (navigator.geolocation) {
        const options = {
            enableHighAccuracy: true,
            maximumAge: 30000,
            timeout: 27000
        };

        navigator.geolocation.getCurrentPosition(
            function(position) {
                // Success handler
                const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;

                // Set latitude and longitude values in form inputs
                latitudeInputs.forEach(input => {
                    if (input) input.value = latitude;
                });

                longitudeInputs.forEach(input => {
                    if (input) input.value = longitude;
                });

                console.log("Geolocation obtained successfully", { latitude, longitude });
            },
            function(error) {
                // Error handler
                console.error('Error getting geolocation:', error);

                let errorMessage = "Unable to get your location. ";

                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage += "Location permission was denied. Please enable location services in your browser settings.";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage += "Location information is unavailable.";
                        break;
                    case error.TIMEOUT:
                        errorMessage += "The request to get your location timed out.";
                        break;
                    case error.UNKNOWN_ERROR:
                        errorMessage += "An unknown error occurred.";
                        break;
                }

                // Create and display error message
                const errorElement = document.createElement('div');
                errorElement.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4';
                errorElement.innerHTML = `<span class="block">${errorMessage}</span>`;

                // Find a suitable parent element to display the error
                const formElement = document.querySelector('form');
                if (formElement) {
                    formElement.prepend(errorElement);
                }
            },
            options
        );
    } else {
        console.error("Geolocation is not supported by this browser");
        alert("Geolocation is not supported by your browser. Please use a modern browser with location services enabled.");
    }
}
