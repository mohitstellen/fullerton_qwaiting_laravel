<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo -->
        <div class="text-center mb-6">
            <img src="{{ url($logo) }}" alt="Fullerton Health" class="mx-auto max-w-xs h-auto" style="max-height: 120px;" />
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
                <div class="pt-2">
                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-base font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 uppercase">
                        OK
                    </button>
                </div>

                <!-- Back to Login Link -->
                <div class="text-center pt-2">
                    <a href="{{ route('tenant.patient.login') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                        Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

