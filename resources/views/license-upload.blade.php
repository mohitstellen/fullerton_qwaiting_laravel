<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload License - Fullerton QWaiting</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background particles */
        body::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            border-radius: 24px;
            position: relative;
            overflow: hidden;
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
            background-size: 200% 100%;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .upload-zone {
            border: 2px dashed #d1d5db;
            border-radius: 16px;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            position: relative;
            overflow: hidden;
        }

        .upload-zone:hover {
            border-color: #667eea;
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .upload-zone.file-selected {
            border-color: #10b981;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            color: white !important;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .info-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 16px;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }

        .info-card::before {
            content: '💡';
            position: absolute;
            font-size: 80px;
            opacity: 0.1;
            right: -10px;
            bottom: -10px;
            transform: rotate(-15deg);
        }

        .icon-upload {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .success-alert {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border: 1px solid #6ee7b7;
            border-radius: 12px;
            padding: 16px;
            color: #065f46;
            animation: slideDown 0.5s ease-out;
            margin-bottom: 24px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-alert {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 1px solid #f87171;
            border-radius: 12px;
            padding: 16px;
            color: #991b1b;
            animation: slideDown 0.5s ease-out;
            margin-bottom: 24px;
        }

        .link-secondary {
            color: #6b7280;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
        }

        .link-secondary::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }

        .link-secondary:hover::after {
            width: 100%;
        }

        .link-secondary:hover {
            color: #667eea;
        }

        .file-input {
            opacity: 0;
            position: absolute;
            z-index: -1;
        }

        .file-input-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-icon {
            font-size: 48px;
            margin-bottom: 16px;
            color: #667eea;
        }

        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>

<body>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
        style="position: relative; z-index: 1;">
        <div style="max-width: 48rem; width: 100%; margin: 10px auto;">
            <div class="glass-card" style="padding: 2.5rem;">
                <!-- Header Section -->
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div class="icon-upload">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                            stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                    </div>
                    <h1 class="gradient-text" style="font-size: 2.25rem; font-weight: 700; margin-bottom: 0.75rem;">
                        Upload License
                    </h1>
                    <p style="color: #6b7280; font-size: 1.125rem;">
                        Activate your application with a valid license file
                    </p>
                </div>

                <!-- Success Alert -->
                @if (session()->has('success'))
                    <div class="success-alert">
                        <div style="display: flex; align-items: center;">
                            <svg style="width: 24px; height: 24px; margin-right: 12px;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span style="font-weight: 600;">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Error Alert -->
                @if (session()->has('error'))
                    <div class="error-alert">
                        <div style="display: flex; align-items: center;">
                            <svg style="width: 24px; height: 24px; margin-right: 12px;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span style="font-weight: 600;">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="error-alert">
                        <div style="display: flex; align-items: flex-start;">
                            <svg style="width: 24px; height: 24px; margin-right: 12px; flex-shrink: 0;" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <ul style="list-style: disc; margin-left: 1rem;">
                                @foreach ($errors->all() as $error)
                                    <li style="font-weight: 600;">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Upload Form -->
                <form action="{{ route('upload-license.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div style="margin-bottom: 2rem;">
                        <div class="upload-zone" id="uploadZone">
                            <input type="file" name="licenseFile" id="licenseFile" accept=".json" class="file-input"
                                required onchange="handleFileSelect(this)" />
                            <label for="licenseFile" class="file-input-label">
                                <div class="file-icon">📄</div>
                                <div style="text-align: center;">
                                    <p
                                        style="font-size: 1.125rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                                        <span style="color: #667eea;">Choose a file</span> or drag it here
                                    </p>
                                    <p style="font-size: 0.875rem; color: #6b7280;" id="fileName">
                                        Accepts .json files only
                                    </p>
                                </div>
                            </label>
                        </div>
                        @error('licenseFile')
                            <div
                                style="margin-top: 12px; font-size: 0.875rem; color: #dc2626; display: flex; align-items: center;">
                                <svg style="width: 16px; height: 16px; margin-right: 8px;" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div
                        style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
                        <button type="submit" class="btn-primary">
                            <span style="display: flex; align-items: center;">
                                <svg style="width: 20px; height: 20px; margin-right: 8px;" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                    </path>
                                </svg>
                                Upload License
                            </span>
                        </button>

                        @auth
                            <a href="{{ route('tenant.dashboard') }}" class="link-secondary">
                                Go to Dashboard →
                            </a>
                        @else
                            <a href="{{ route('tenant.login') }}" class="link-secondary">
                                Go to Login →
                            </a>
                        @endauth
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function handleFileSelect(input) {
            const uploadZone = document.getElementById('uploadZone');
            const fileName = document.getElementById('fileName');

            if (input.files && input.files[0]) {
                uploadZone.classList.add('file-selected');
                fileName.textContent = '📁 ' + input.files[0].name;
            } else {
                uploadZone.classList.remove('file-selected');
                fileName.textContent = 'Accepts .json files only';
            }
        }

        // Drag and drop functionality
        const uploadZone = document.getElementById('uploadZone');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadZone.style.borderColor = '#667eea';
                uploadZone.style.transform = 'scale(1.02)';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadZone.style.borderColor = '#d1d5db';
                uploadZone.style.transform = 'scale(1)';
            }, false);
        });
    </script>
</body>

</html>