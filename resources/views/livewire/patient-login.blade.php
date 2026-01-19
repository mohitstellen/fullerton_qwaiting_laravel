<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-6">

        <!-- Logo -->
        <div class="text-center mb-6">
            <img src="{{ url($logo) }}" alt="Fullerton Health" class="mx-auto" style="max-height: 90px;">
        </div>

        <div>
            @if (session()->has('error'))
                <div class="bg-red-50 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if (session()->has('message'))
                <div class="bg-green-50 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            @if($otpSent)
                <!-- Verify OTP Section -->
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Verify OTP</h2>
                    <p class="text-gray-600 mt-2">Enter the code sent to your {{ $loginMethod }}.</p>
                </div>

                <form wire:submit.prevent="verifyOtp" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700">One-Time Password</label>
                        <input type="text" wire:model="otp" placeholder="Enter OTP"
                            class="w-full h-10 rounded border border-gray-300 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition duration-200">
                        @error('otp') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded font-semibold transition duration-200">
                        Verify
                    </button>

                    <button type="button" wire:click="resetLogin" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-2.5 rounded font-semibold transition duration-200">
                        Back
                    </button>
                </form>

            @else
                <!-- Login Selection Section -->
                <div class="text-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Login with Email or Phone</h2>
                </div>

                <form wire:submit.prevent="sendOtp" class="space-y-5">
                    
                    <!-- Login Method -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Login Method</label>
                        <div class="flex items-center space-x-6">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" wire:model.live="loginMethod" value="phone" class="form-radio text-blue-600 focus:ring-blue-500 h-4 w-4 border border-gray-300">
                                <span class="ml-2 text-gray-700">Phone</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" wire:model.live="loginMethod" value="email" class="form-radio text-blue-600 focus:ring-blue-500 h-4 w-4 border border-gray-300">
                                <span class="ml-2 text-gray-700">Email</span>
                            </label>
                        </div>
                    </div>

                    @if($loginMethod === 'phone')
                        <!-- Phone Number -->
                        <div class="animate-fade-in-down">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Phone Number</label>
                            <input type="text" wire:model="mobile_number" placeholder="+1 234 567 890"
                                class="w-full h-10 rounded border border-gray-300 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition duration-200 @error('mobile_number') border-red-500 @enderror">
                            @error('mobile_number') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- OTP Method -->
                        <div class="animate-fade-in-down">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Receive OTP via</label>
                            <div class="flex items-center space-x-6">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="radio" wire:model="otpMethod" value="whatsapp" class="form-radio text-blue-600 focus:ring-blue-500 h-4 w-4 border border-gray-300">
                                    <span class="ml-2 text-gray-700">WhatsApp</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="radio" wire:model="otpMethod" value="sms" class="form-radio text-blue-600 focus:ring-blue-500 h-4 w-4 border border-gray-300">
                                    <span class="ml-2 text-gray-700">SMS</span>
                                </label>
                            </div>
                            @error('otpMethod') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <!-- Email Address -->
                        <div class="animate-fade-in-down">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Email Address</label>
                            <input type="email" wire:model="email" placeholder="you@example.com"
                                class="w-full h-10 rounded border border-gray-300 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition duration-200 @error('email') border-red-500 @enderror">
                            @error('email') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded font-semibold transition duration-200 shadow-md">
                        Send OTP
                    </button>

                    <!-- Links -->
                    <div class="flex justify-between text-sm pt-2">
                        <a href="{{ route('tenant.patient.register') }}" class="text-blue-600 hover:text-blue-800 transition">
                            Sign Up
                        </a>
                        <a href="{{ route('tenant.patient.forgot-password') }}" class="text-blue-600 hover:text-blue-800 transition">
                            Forgot Login ID?
                        </a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>