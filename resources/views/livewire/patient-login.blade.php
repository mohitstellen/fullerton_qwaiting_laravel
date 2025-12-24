<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-6">

    <!-- Logo -->
    <div class="text-center mb-4">
        <img src="{{ url($logo) }}" alt="Fullerton Health"
             class="mx-auto"
             style="max-height: 90px;">
    </div>

    <form wire:submit.prevent="login" class="space-y-4">

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
        </div>

        <!-- Mobile -->
        <div>
            <label class="block text-sm font-medium mb-1">
                Country Code & Mobile Number
            </label>
            <input type="text"
                   wire:model.live="mobile_number"
                   placeholder="65XXXXXXXX"
                   class="w-full h-10 rounded border px-3">
        </div>

        <!-- Password -->
        <div>
            <label class="block text-sm font-medium mb-1">
                Password
            </label>
            <input type="password"
                   wire:model.live="password"
                   class="w-full h-10 rounded border px-3">
        </div>

        <!-- Button -->
        <button type="submit"
                class="w-full bg-blue-600 text-white py-2.5 rounded font-semibold">
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
