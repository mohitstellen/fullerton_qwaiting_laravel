<!DOCTYPE html>
<html lang="{{ session('app_locale', app()->getLocale()) }}" dir="{{ session('app_locale') === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Import Staff') }} - Qwaiting</title>
    <link rel="icon" href="{{ url('images/favicon.ico') }}" />
    
    {{-- Tailwind CSS 3 CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    
    @stack('styles')
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            {{-- Header Section --}}
            <div class="mb-8">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                                {{ __('Import Staff') }}
                            </h1>
                            <p class="text-gray-600">Upload a CSV or Excel file to import staff members into your system</p>
                            
                            @auth
                                <div class="mt-2 text-xs text-green-600">
                                    <svg class="inline h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Logged in as: {{ Auth::user()->name }} (ID: {{ Auth::user()->id }})
                                    @if(Auth::user()->team_id)
                                        | Team ID: {{ Auth::user()->team_id }}
                                    @endif
                                    @if(function_exists('tenant') && tenant('id'))
                                        | Tenant ID: {{ tenant('id') }}
                                    @endif
                                </div>
                            @else
                                <div class="mt-2 text-xs text-red-600">
                                    <svg class="inline h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Not logged in - please <a href="{{ route('tenant.login') }}" class="underline">login</a> to import staff
                                </div>
                            @endauth
                        </div>
                        <a href="#" onclick="event.preventDefault(); downloadSampleCSV();" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow-sm transition-colors">
                            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download Sample CSV
                        </a>
                    </div>
                </div>
            </div>

            {{-- Success Message --}}
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 rounded-lg shadow-sm p-4" role="alert">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-semibold text-green-800">Success!</h3>
                            <p class="mt-1 text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- General Error Message --}}
            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg shadow-sm p-4" role="alert">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-semibold text-red-800">Error!</h3>
                            <p class="mt-1 text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Validation Errors --}}
            @if(isset($errors) && $errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg shadow-sm p-4">
                    <h3 class="text-sm font-semibold text-red-800 mb-2">Validation Errors:</h3>
                    <ul class="mt-2 space-y-1 list-disc list-inside text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Validation Errors from Session --}}
            @if(session('validation_errors'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg shadow-sm p-4">
                    <h3 class="text-sm font-semibold text-red-800 mb-2">Validation Errors:</h3>
                    <ul class="mt-2 space-y-1 list-disc list-inside text-sm text-red-700">
                        @foreach(session('validation_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Import Failures --}}
            @if(session('import_failures'))
                <div class="mb-6 bg-orange-50 border-l-4 border-orange-500 rounded-lg shadow-sm p-4">
                    <h3 class="text-sm font-semibold text-orange-800 mb-3">Some rows failed to import:</h3>
                    <div class="max-h-60 overflow-y-auto">
                        <ul class="space-y-2 text-sm list-disc list-inside text-orange-700">
                            @foreach(session('import_failures') as $failure)
                                <li class="py-1">
                                    <span class="font-medium">Row {{ $failure['row'] }}:</span>
                                    @foreach($failure['errors'] as $error)
                                        <span class="text-orange-600">{{ $error }}</span>
                                    @endforeach
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Main Form Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <form action="{{ route('tenant.import.staff.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6" id="importForm">
                    @csrf
                    
                    {{-- File Upload Section --}}
                    <div class="space-y-2">
                        <label for="file" class="block text-sm font-semibold text-gray-700">
                            Select File (CSV, Excel)
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                            <div class="space-y-1 text-center w-full">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload a file</span>
                                        <input type="file" name="file" id="file" required accept=".csv,.xlsx,.xls" class="sr-only" />
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">CSV, XLSX, XLS up to 10MB</p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Supported formats: .csv, .xlsx, .xls</p>
                    </div>

                    {{-- CSV Format Instructions --}}
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5">
                        <div class="flex items-center mb-3">
                            <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-sm font-bold text-gray-800">CSV File Format Requirements</h3>
                        </div>
                        <p class="text-xs text-gray-700 mb-4">Your CSV file should have the following columns (first row should be headers):</p>
                        <div class="overflow-x-auto -mx-2">
                            <div class="inline-block min-w-full align-middle">
                                <div class="overflow-hidden shadow-sm ring-1 ring-black ring-opacity-5 rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-blue-600">
                                            <tr>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">loginname</th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">firstname</th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">email</th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">mobilenumber</th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">gender</th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">rolename</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 whitespace-nowrap text-xs font-medium text-gray-900">john.doe</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-700">John Doe</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-700">john@example.com</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-700">1234567890</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-700">Male</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-700">Staff</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 p-3 bg-blue-100 rounded-md border border-blue-200">
                            <p class="text-xs text-gray-800">
                                <span class="font-semibold">Important Notes:</span>
                                <ul class="mt-1 ml-4 list-disc space-y-1 text-gray-700">
                                    <li><span class="font-medium">loginname</span> and <span class="font-medium">email</span> are required fields</li>
                                    <li>Other fields (firstname, mobilenumber, gender, rolename) are optional</li>
                                    <li>Default password for all imported users is: <span class="font-bold text-blue-700">12345678</span></li>
                                </ul>
                            </p>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <a href="{{ route('tenant.staff.list') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Cancel
                        </a>
                        <button type="submit" id="submitBtn" 
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-lg shadow-lg font-semibold text-sm text-white uppercase tracking-wider hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform transition-all duration-150 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                            <svg id="btnIcon" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span id="btnText">Import Staff</span>
                            <svg id="btnSpinner" class="hidden ml-2 h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('importForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            const btnIcon = document.getElementById('btnIcon');
            
            btn.disabled = true;
            btnText.textContent = 'Importing...';
            btnIcon.classList.add('hidden');
            btnSpinner.classList.remove('hidden');
        });

        // File input change handler for better UX
        const fileInput = document.getElementById('file');
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name;
                if (fileName) {
                    const label = fileInput.closest('form').querySelector('label[for="file"]');
                    if (label) {
                        const fileInfo = document.createElement('span');
                        fileInfo.className = 'text-xs text-blue-600 font-medium ml-2';
                        fileInfo.textContent = `Selected: ${fileName}`;
                        // Remove previous file info if exists
                        const prevInfo = label.querySelector('.text-blue-600');
                        if (prevInfo) prevInfo.remove();
                        label.appendChild(fileInfo);
                    }
                }
            });
        }

        // Download sample CSV function
        function downloadSampleCSV() {
            const csvContent = "loginname,firstname,email,mobilenumber,gender,rolename\n" +
                               "john.doe,John Doe,john.doe@example.com,1234567890,Male,Staff\n" +
                               "jane.smith,Jane Smith,jane.smith@example.com,0987654321,Female,Staff\n" +
                               "admin.user,Admin User,admin@example.com,1122334455,Male,Admin";
            
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            
            link.setAttribute("href", url);
            link.setAttribute("download", "staff_import_sample.csv");
            link.style.visibility = 'hidden';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
    @stack('scripts')
</body>
</html>
