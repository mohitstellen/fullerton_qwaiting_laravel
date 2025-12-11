<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <style>
        /* Ensure radio buttons are visible */
        input[type="radio"] {
            appearance: auto;
            -webkit-appearance: radio;
            -moz-appearance: radio;
            width: 1rem;
            height: 1rem;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            background-color: white;
            cursor: pointer;
        }
        input[type="radio"]:checked {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        input[type="radio"]:focus {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
        }
    </style>
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

        <!-- Login Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
            <form wire:submit.prevent="login" class="space-y-6">
                <!-- Customer Type Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Select Customer Type
                    </label>
                    <div class="flex gap-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="customer_type" value="Private" 
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 cursor-pointer"
                                style="accent-color: #2563eb;">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Private Customer</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="customer_type" value="Corporate" 
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 cursor-pointer"
                                style="accent-color: #2563eb;">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Corporate Customer</span>
                        </label>
                    </div>
                    @error('customer_type')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Mobile Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Country Code & Mobile Number
                    </label>
                    <input type="text" wire:model.live="mobile_number" 
                        name="mobile_number"
                        placeholder="Country Code & Mobile Number (e.g. 65XXXXXXXX)"
                        class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
                    @error('mobile_number')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        @if($showPassword)
                            <input type="text" wire:model.live="password" 
                                placeholder="Password"
                                class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 pr-10 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
                        @else
                            <input type="password" wire:model.live="password" name="password" 
                                placeholder="Password"
                                class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 pr-10 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
                        @endif
                        <button type="button" wire:click="togglePassword" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            @if($showPassword)
                                <!-- Eye Closed Icon (Hide Password) -->
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            @else
                                <!-- Eye Open Icon (Show Password) -->
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            @endif
                        </button>
                    </div>
                    @error('password')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Login Button -->
                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        LOGIN
                    </button>
                </div>

                <!-- Links -->
                <div class="flex justify-between text-sm">
                    <a href="{{ route('tenant.patient.register') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                        Sign Up
                    </a>
                    <a href="{{ route('tenant.patient.forgot-password') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                        Forgot Login ID / Password
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

