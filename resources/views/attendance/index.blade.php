<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Today's Attendance</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ now()->format('l, F j, Y') }}</p>
                    </div>

                    <!-- Attendance Method Toggle -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-md font-medium text-gray-900">Attendance Method</h4>
                                <p class="text-sm text-gray-600">Choose how you want to record your attendance</p>
                            </div>
                            <div class="flex items-center">
                                <label for="attendance-method-toggle" class="inline-flex relative items-center cursor-pointer">
                                    <input type="checkbox" id="attendance-method-toggle" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900" id="attendance-method-label">Standard Verification</span>
                                </label>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div id="standard-method-description" class="text-sm text-gray-600">
                                Using standard verification with location and photo capture.
                            </div>
                            <div id="alternative-method-description" class="text-sm text-gray-600 hidden">
                                <span class="text-amber-600 font-semibold">Testing Mode:</span> Using alternative method without location or photo verification.
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Check In</h4>

                            @if ($attendance && $attendance->check_in)
                                <div class="bg-green-50 border border-green-200 rounded p-4">
                                    <p class="text-green-700">You have checked in at {{ \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') }}</p>

                                    @if($attendance->check_in_method === 'alternative')
                                        <p class="text-amber-600 text-sm mt-1">
                                            <span class="inline-flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Alternative method used (no verification)
                                            </span>
                                        </p>
                                    @elseif ($attendance->check_in_photo)
                                        <div class="mt-2">
                                            <img src="{{ Storage::url($attendance->check_in_photo) }}" alt="Check-in Photo" class="w-32 h-32 object-cover rounded">
                                        </div>
                                    @endif
                                </div>
                            @else
                                <!-- Standard Method UI -->
                                <div id="standard-check-in-ui">
                                    <div id="check-in-container">
                                        <!-- Step 1: Location Verification -->
                                        <div id="location-verification-step" class="mb-6">
                                            <h5 class="text-sm font-medium text-gray-700 mb-2">Step 1: Verify Your Location</h5>
                                            <p class="text-sm text-gray-600 mb-4">
                                                First, we need to verify that you're at the office. Please allow location access when prompted.
                                            </p>

                                            <div id="location-status" class="mb-4 p-3 border rounded bg-gray-50">
                                                <p class="text-sm">Waiting for location verification...</p>
                                            </div>

                                            <button type="button" id="verify-location-btn" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                Verify My Location
                                            </button>
                                        </div>

                                        <!-- Step 2: Photo Capture (Initially Hidden) -->
                                        <div id="photo-capture-step" class="hidden">
                                            <h5 class="text-sm font-medium text-gray-700 mb-2">Step 2: Take Your Photo</h5>
                                            <p class="text-sm text-gray-600 mb-4">
                                                Now, please take a photo to complete your check-in.
                                            </p>

                                            <form action="{{ route('attendance.check-in') }}" method="POST" enctype="multipart/form-data" id="check-in-form">
                                                @csrf

                                                <div class="mb-4">
                                                    <div class="flex flex-col items-center">
                                                        <div class="w-full max-w-md bg-gray-100 rounded-lg overflow-hidden mb-2">
                                                            <video id="camera-feed" class="w-full h-48 object-cover" autoplay></video>
                                                            <canvas id="photo-canvas" class="hidden w-full h-48 object-cover"></canvas>
                                                        </div>

                                                        <!-- Manual file upload fallback -->
                                                        <div id="manual-upload" class="hidden w-full mb-2">
                                                            <p class="text-sm text-gray-600 mb-2">If the camera doesn't work, you can manually upload a photo:</p>
                                                            <input type="file" name="photo_manual" accept="image/*" class="w-full text-sm">
                                                        </div>

                                                        <div class="flex space-x-2">
                                                            <button type="button" id="capture-btn" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                                Capture Photo
                                                            </button>
                                                            <button type="button" id="retake-btn" class="hidden px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                                                Retake
                                                            </button>
                                                            <button type="button" id="toggle-manual-btn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                                                Upload Instead
                                                            </button>
                                                        </div>

                                                        <input type="file" id="photo" name="photo" accept="image/*" capture="user" class="hidden">
                                                    </div>
                                                </div>

                                                <input type="hidden" id="latitude" name="latitude">
                                                <input type="hidden" id="longitude" name="longitude">

                                                <div class="flex justify-end">
                                                    <button type="submit" id="submit-btn" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2" disabled>
                                                        Complete Check In
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Alternative Method UI -->
                                <div id="alternative-check-in-ui" class="hidden">
                                    <div class="bg-amber-50 border border-amber-200 rounded p-4 mb-4">
                                        <div class="flex items-start">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 mt-0.5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div>
                                                <p class="text-amber-800 font-medium">Testing Mode</p>
                                                <p class="text-amber-700 text-sm">You are using the alternative check-in method. No location or photo verification will be performed.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <form action="{{ route('attendance.alternative-check-in') }}" method="POST" id="alternative-check-in-form">
                                        @csrf
                                        <div class="flex justify-center">
                                            <button type="submit" class="px-6 py-3 bg-amber-600 text-white rounded-lg hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                                                Record Check-In (No Verification)
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Check Out</h4>

                            @if (!$attendance || !$attendance->check_in)
                                <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                                    <p class="text-yellow-700">You need to check in first before checking out.</p>
                                </div>
                            @elseif ($attendance && $attendance->check_out)
                                <div class="bg-green-50 border border-green-200 rounded p-4">
                                    <p class="text-green-700">You have checked out at {{ \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') }}</p>

                                    @if($attendance->check_out_method === 'alternative')
                                        <p class="text-amber-600 text-sm mt-1">
                                            <span class="inline-flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Alternative method used (no verification)
                                            </span>
                                        </p>
                                    @elseif ($attendance->check_out_photo)
                                        <div class="mt-2">
                                            <img src="{{ Storage::url($attendance->check_out_photo) }}" alt="Check-out Photo" class="w-32 h-32 object-cover rounded">
                                        </div>
                                    @endif
                                </div>
                            @else
                                <!-- Standard Method UI -->
                                <div id="standard-check-out-ui">
                                    <div id="check-out-container">
                                        <!-- Step 1: Location Verification -->
                                        <div id="location-verification-step-out" class="mb-6">
                                            <h5 class="text-sm font-medium text-gray-700 mb-2">Step 1: Verify Your Location</h5>
                                            <p class="text-sm text-gray-600 mb-4">
                                                First, we need to verify that you're at the office. Please allow location access when prompted.
                                            </p>

                                            <div id="location-status-out" class="mb-4 p-3 border rounded bg-gray-50">
                                                <p class="text-sm">Waiting for location verification...</p>
                                            </div>

                                            <button type="button" id="verify-location-btn-out" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                Verify My Location
                                            </button>
                                        </div>

                                        <!-- Step 2: Photo Capture (Initially Hidden) -->
                                        <div id="photo-capture-step-out" class="hidden">
                                            <h5 class="text-sm font-medium text-gray-700 mb-2">Step 2: Take Your Photo</h5>
                                            <p class="text-sm text-gray-600 mb-4">
                                                Now, please take a photo to complete your check-out.
                                            </p>

                                            <form action="{{ route('attendance.check-out') }}" method="POST" enctype="multipart/form-data" id="check-out-form">
                                                @csrf

                                                <div class="mb-4">
                                                    <div class="flex flex-col items-center">
                                                        <div class="w-full max-w-md bg-gray-100 rounded-lg overflow-hidden mb-2">
                                                            <video id="camera-feed-out" class="w-full h-48 object-cover" autoplay></video>
                                                            <canvas id="photo-canvas-out" class="hidden w-full h-48 object-cover"></canvas>
                                                        </div>

                                                        <!-- Manual file upload fallback -->
                                                        <div id="manual-upload-out" class="hidden w-full mb-2">
                                                            <p class="text-sm text-gray-600 mb-2">If the camera doesn't work, you can manually upload a photo:</p>
                                                            <input type="file" name="photo_manual" accept="image/*" class="w-full text-sm">
                                                        </div>

                                                        <div class="flex space-x-2">
                                                            <button type="button" id="capture-btn-out" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                                Capture Photo
                                                            </button>
                                                            <button type="button" id="retake-btn-out" class="hidden px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                                                Retake
                                                            </button>
                                                            <button type="button" id="toggle-manual-btn-out" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                                                Upload Instead
                                                            </button>
                                                        </div>

                                                        <input type="file" id="photo-out" name="photo" accept="image/*" capture="user" class="hidden">
                                                    </div>
                                                </div>

                                                <input type="hidden" id="latitude-out" name="latitude">
                                                <input type="hidden" id="longitude-out" name="longitude">

                                                <div class="flex justify-end">
                                                    <button type="submit" id="submit-btn-out" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" disabled>
                                                        Complete Check Out
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Alternative Method UI -->
                                <div id="alternative-check-out-ui" class="hidden">
                                    <div class="bg-amber-50 border border-amber-200 rounded p-4 mb-4">
                                        <div class="flex items-start">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 mt-0.5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div>
                                                <p class="text-amber-800 font-medium">Testing Mode</p>
                                                <p class="text-amber-700 text-sm">You are using the alternative check-out method. No location or photo verification will be performed.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <form action="{{ route('attendance.alternative-check-out') }}" method="POST" id="alternative-check-out-form">
                                        @csrf
                                        <div class="flex justify-center">
                                            <button type="submit" class="px-6 py-3 bg-amber-600 text-white rounded-lg hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                                                Record Check-Out (No Verification)
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('attendance.history') }}" class="text-blue-600 hover:text-blue-800">
                            View Attendance History →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle between standard and alternative methods
            const methodToggle = document.getElementById('attendance-method-toggle');
            const methodLabel = document.getElementById('attendance-method-label');
            const standardDesc = document.getElementById('standard-method-description');
            const alternativeDesc = document.getElementById('alternative-method-description');

            // Check-in UI elements
            const standardCheckInUI = document.getElementById('standard-check-in-ui');
            const alternativeCheckInUI = document.getElementById('alternative-check-in-ui');

            // Check-out UI elements
            const standardCheckOutUI = document.getElementById('standard-check-out-ui');
            const alternativeCheckOutUI = document.getElementById('alternative-check-out-ui');

            if (methodToggle) {
                methodToggle.addEventListener('change', function() {
                    if (this.checked) {
                        // Standard method
                        methodLabel.textContent = 'Standard Verification';
                        standardDesc.classList.remove('hidden');
                        alternativeDesc.classList.add('hidden');

                        // Show standard UI, hide alternative UI
                        if (standardCheckInUI) standardCheckInUI.classList.remove('hidden');
                        if (alternativeCheckInUI) alternativeCheckInUI.classList.add('hidden');
                        if (standardCheckOutUI) standardCheckOutUI.classList.remove('hidden');
                        if (alternativeCheckOutUI) alternativeCheckOutUI.classList.add('hidden');
                    } else {
                        // Alternative method
                        methodLabel.textContent = 'Alternative Method (Testing)';
                        standardDesc.classList.add('hidden');
                        alternativeDesc.classList.remove('hidden');

                        // Show alternative UI, hide standard UI
                        if (standardCheckInUI) standardCheckInUI.classList.add('hidden');
                        if (alternativeCheckInUI) alternativeCheckInUI.classList.remove('hidden');
                        if (standardCheckOutUI) standardCheckOutUI.classList.add('hidden');
                        if (alternativeCheckOutUI) alternativeCheckOutUI.classList.remove('hidden');
                    }
                });
            }

            // Location verification for check-in
            setupLocationVerification('verify-location-btn', 'location-status', 'location-verification-step', 'photo-capture-step', 'latitude', 'longitude');

            // Location verification for check-out
            setupLocationVerification('verify-location-btn-out', 'location-status-out', 'location-verification-step-out', 'photo-capture-step-out', 'latitude-out', 'longitude-out');

            // Camera setup for check-in
            setupCamera('camera-feed', 'photo-canvas', 'capture-btn', 'retake-btn', 'photo', 'submit-btn', 'toggle-manual-btn', 'manual-upload');

            // Camera setup for check-out
            setupCamera('camera-feed-out', 'photo-canvas-out', 'capture-btn-out', 'retake-btn-out', 'photo-out', 'submit-btn-out', 'toggle-manual-btn-out', 'manual-upload-out');
        });

        /**
         * Set up location verification process
         */
        function setupLocationVerification(btnId, statusId, stepId, nextStepId, latitudeId, longitudeId) {
            const verifyBtn = document.getElementById(btnId);
            const statusEl = document.getElementById(statusId);
            const stepEl = document.getElementById(stepId);
            const nextStepEl = document.getElementById(nextStepId);
            const latitudeInput = document.getElementById(latitudeId);
            const longitudeInput = document.getElementById(longitudeId);

            if (!verifyBtn || !statusEl) return;

            verifyBtn.addEventListener('click', function() {
                statusEl.innerHTML = '<p class="text-sm text-blue-600">Getting your location...</p>';
                verifyBtn.disabled = true;
                verifyBtn.classList.add('opacity-50');

                if (!navigator.geolocation) {
                    statusEl.innerHTML = '<p class="text-sm text-red-600">Geolocation is not supported by your browser</p>';
                    verifyBtn.disabled = false;
                    verifyBtn.classList.remove('opacity-50');
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    // Success callback
                    function(position) {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;

                        // Store coordinates in hidden inputs
                        latitudeInput.value = latitude;
                        longitudeInput.value = longitude;

                        // Verify location with server
                        statusEl.innerHTML = '<p class="text-sm text-blue-600">Verifying your location...</p>';

                        fetch('{{ route("attendance.verify-location") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                latitude: latitude,
                                longitude: longitude
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Location verified successfully
                                statusEl.innerHTML = '<p class="text-sm text-green-600">✓ Location verified! You are at the office.</p>';

                                // Show next step (photo capture)
                                setTimeout(() => {
                                    stepEl.classList.add('hidden');
                                    nextStepEl.classList.remove('hidden');
                                }, 1000);
                            } else {
                                // Location verification failed
                                statusEl.innerHTML = `<p class="text-sm text-red-600">✗ ${data.message}</p>`;
                                verifyBtn.disabled = false;
                                verifyBtn.classList.remove('opacity-50');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            statusEl.innerHTML = '<p class="text-sm text-red-600">✗ Error verifying location. Please try again.</p>';
                            verifyBtn.disabled = false;
                            verifyBtn.classList.remove('opacity-50');
                        });
                    },
                    // Error callback
                    function(error) {
                        let errorMessage = 'Error getting your location: ';

                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage += 'Location permission denied. Please enable location services.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage += 'Location information is unavailable.';
                                break;
                            case error.TIMEOUT:
                                errorMessage += 'Location request timed out.';
                                break;
                            default:
                                errorMessage += 'Unknown error occurred.';
                                break;
                        }

                        statusEl.innerHTML = `<p class="text-sm text-red-600">✗ ${errorMessage}</p>`;
                        verifyBtn.disabled = false;
                        verifyBtn.classList.remove('opacity-50');
                    },
                    // Options
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });
        }

        /**
         * Set up camera functionality
         */
        function setupCamera(videoId, canvasId, captureBtnId, retakeBtnId, fileInputId, submitBtnId, toggleManualBtnId, manualUploadId) {
            const video = document.getElementById(videoId);
            const canvas = document.getElementById(canvasId);
            const captureBtn = document.getElementById(captureBtnId);
            const retakeBtn = document.getElementById(retakeBtnId);
            const fileInput = document.getElementById(fileInputId);
            const submitBtn = document.getElementById(submitBtnId);
            const toggleManualBtn = document.getElementById(toggleManualBtnId);
            const manualUpload = document.getElementById(manualUploadId);

            if (!video || !canvas || !captureBtn) return;

            let stream;

            // Start camera
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({
                    video: {
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        facingMode: "user"
                    }
                })
                .then(function(mediaStream) {
                    stream = mediaStream;
                    video.srcObject = mediaStream;
                    video.onloadedmetadata = function(e) {
                        video.play();
                    };
                })
                .catch(function(error) {
                    console.error('Error accessing camera:', error);

                    // Show manual upload option if camera fails
                    if (manualUpload) {
                        manualUpload.classList.remove('hidden');
                        video.parentNode.classList.add('hidden');

                        if (toggleManualBtn) {
                            toggleManualBtn.textContent = 'Try Camera';
                        }
                    }
                });
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

                        // Create a FileList-like object
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
                });
            }

            // Toggle between camera and manual upload
            if (toggleManualBtn && manualUpload) {
                toggleManualBtn.addEventListener('click', function() {
                    if (manualUpload.classList.contains('hidden')) {
                        manualUpload.classList.remove('hidden');
                        video.parentNode.classList.add('hidden');
                        toggleManualBtn.textContent = 'Use Camera';

                        // Enable submit button when using manual upload
                        submitBtn.disabled = false;
                    } else {
                        manualUpload.classList.add('hidden');
                        video.parentNode.classList.remove('hidden');
                        toggleManualBtn.textContent = 'Upload Instead';

                        // Disable submit button until photo is captured
                        submitBtn.disabled = true;
                    }
                });
            }
        }
    </script>
    @endpush
</x-app-layout>
