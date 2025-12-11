<div class="px-4 py-6 sm:px-0">
    <div class="max-w-2xl mx-auto">
        @if (session()->has('error'))
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-900/30 dark:text-red-200 mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if (session()->has('success'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/30 dark:text-green-200 mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Change Password Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">Change Password</h2>

            @if($isTemporaryPassword)
                <div class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800 dark:border-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-200 mb-6">
                    <strong>⚠️ Temporary Password Detected:</strong> You are using a temporary password. Please change it to a secure password of your choice.
                </div>
            @endif

            <form wire:submit.prevent="changePassword" class="space-y-6">
                <!-- Current Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Current Password
                    </label>
                    <div class="relative">
                        @if($showCurrentPassword)
                            <input type="text" wire:model.live="current_password" 
                                placeholder="Current Password"
                                class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 pr-10 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
                        @else
                            <input type="password" wire:model.live="current_password" 
                                placeholder="Current Password"
                                class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 pr-10 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
                        @endif
                        <button type="button" wire:click="toggleCurrentPassword" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            @if($showCurrentPassword)
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            @else
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            @endif
                        </button>
                    </div>
                    @error('current_password')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        New Password
                    </label>
                    <div class="relative">
                        @if($showNewPassword)
                            <input type="text" wire:model.live="new_password" 
                                placeholder="New Password"
                                class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 pr-10 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
                        @else
                            <input type="password" wire:model.live="new_password" 
                                placeholder="New Password"
                                class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 pr-10 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
                        @endif
                        <button type="button" wire:click="toggleNewPassword" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            @if($showNewPassword)
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            @else
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            @endif
                        </button>
                    </div>
                    @error('new_password')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Password must be at least 8 characters long.</p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Confirm New Password
                    </label>
                    <div class="relative">
                        @if($showConfirmPassword)
                            <input type="text" wire:model.live="confirm_password" 
                                placeholder="Confirm New Password"
                                class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 pr-10 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
                        @else
                            <input type="password" wire:model.live="confirm_password" 
                                placeholder="Confirm New Password"
                                class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 pr-10 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
                        @endif
                        <button type="button" wire:click="toggleConfirmPassword" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            @if($showConfirmPassword)
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            @else
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            @endif
                        </button>
                    </div>
                    @error('confirm_password')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Change Password
                    </button>
                </div>

                <!-- Back to Dashboard Link -->
                <div class="text-center">
                    <a href="{{ route('tenant.patient.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

