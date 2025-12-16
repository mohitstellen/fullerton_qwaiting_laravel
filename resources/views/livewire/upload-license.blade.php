<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload License - Fullerton QWaiting</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Upload License</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Upload a valid license file (license.json) to activate or renew your application license.
                    </p>
                </div>

                @if (session()->has('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                <form wire:submit.prevent="upload">
                    <div class="mb-6">
                        <label for="licenseFile"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            License File (JSON)
                        </label>
                        <input type="file" wire:model="licenseFile" id="licenseFile" accept=".json"
                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 p-2.5" />
                        @error('licenseFile')
                            <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div wire:loading wire:target="licenseFile" class="mb-4">
                        <div class="text-sm text-blue-600">
                            Processing file...
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" wire:loading.attr="disabled" wire:target="upload"
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="upload">Upload License</span>
                            <span wire:loading wire:target="upload">Uploading...</span>
                        </button>

                        @auth
                            <a href="{{ route('tenant.dashboard') }}"
                                class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('tenant.login') }}"
                                class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                                Go to Login
                            </a>
                        @endauth
                    </div>
                </form>

                <div
                    class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <h3 class="font-semibold text-blue-900 dark:text-blue-200 mb-2">Important Notes:</h3>
                    <ul class="list-disc list-inside text-sm text-blue-800 dark:text-blue-300 space-y-1">
                        <li>Only upload license files provided by your vendor</li>
                        <li>The file must be in JSON format</li>
                        <li>Invalid or tampered files will be rejected</li>
                        <li>The new license will take effect immediately upon successful upload</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
</body>

</html>