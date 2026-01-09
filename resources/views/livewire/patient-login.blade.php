<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-6">

        <!-- Logo -->
        <div class="text-center mb-4">
            <img src="{{ url($logo) }}" alt="Fullerton Health" class="mx-auto" style="max-height: 90px;">
        </div>

        <form wire:submit.prevent="login" class="space-y-4">

            <!-- Error Messages -->
            @if (session()->has('error'))
                <div class="bg-red-50 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Customer Type -->
            <div>
                <label class="block text-sm font-medium mb-2">
                    Select Customer Type
                </label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2">
                        <input type="radio" wire:model.live="customer_type" value="Private">
                        Private Customer
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" wire:model.live="customer_type" value="Corporate">
                        Corporate Customer
                    </label>
                </div>
                @error('customer_type')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Mobile -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Country Code & Mobile Number
                </label>
                <input type="text" wire:model.live="mobile_number" placeholder="65XXXXXXXX"
                    class="w-full h-10 rounded border px-3 @error('mobile_number') border-red-500 @enderror">
                @error('mobile_number')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Password
                </label>
                <input type="password" wire:model.live="password"
                    class="w-full h-10 rounded border px-3 @error('password') border-red-500 @enderror">
                @error('password')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Button -->
            <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded font-semibold">
                LOGIN
            </button>

            <!-- Links -->
            <div class="flex justify-between text-sm pt-1">
                <a href="{{ route('tenant.patient.register') }}" class="text-blue-600">
                    Sign Up
                </a>
                <a href="{{ route('tenant.patient.forgot-password') }}" class="text-blue-600">
                    Forgot Login ID / Password
                </a>
            </div>

        </form>
    </div>
</div>