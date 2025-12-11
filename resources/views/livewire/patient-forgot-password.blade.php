<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo -->
        <div class="text-center">
            <img src="{{ url($logo) }}" alt="Fullerton Health" class="mx-auto h-24 w-auto mb-6" />
        </div>

        @if (session()->has('error'))
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-900/30 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        @if (session()->has('success'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/30 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        <!-- Forgot Password Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Forgot Login ID / Password</h2>
                <button wire:click="close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="sendTemporaryPassword" class="space-y-6">
                <!-- Instruction -->
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    Please enter your email or mobile number.
                </p>

                <!-- Email / Mobile Number Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email / Mobile number <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model.live="email_or_mobile" 
                        name="email_or_mobile"
                        placeholder="Email / Mobile number"
                        class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
                    @error('email_or_mobile')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                    <p class="text-xs text-red-600 mt-1">Note : Enter mobile number with country code.</p>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        OK
                    </button>
                </div>

                <!-- Back to Login Link -->
                <div class="text-center">
                    <a href="{{ route('tenant.patient.login') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                        Back to Login
                    </a>
                </div>
            </form>
        </div>

        <!-- Browser Recommendation -->
        <p class="text-center text-xs text-gray-500 dark:text-gray-400">
            Best viewed in Chrome, Edge and Mozilla Firefox
        </p>
    </div>
</div>

