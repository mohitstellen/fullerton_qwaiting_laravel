<div class="p-4">
    <h2 class="text-xl font-semibold mb-4">{{ __('text.Edit Role') }}</h2>
    <div class="rounded-lg shadow border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-6">

        <form wire:submit.prevent="update">
            <div class="mb-4">
                <label class="block mb-1 text-sm font-medium">{{ __('text.Role Name') }} <span class="text-red-500">*</span></label>
                <input type="text" wire:model="name" class="w-full px-4 py-2 border rounded-lg dark:bg-dark-900 dark:border-gray-700 dark:text-white">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block mb-3 text-sm font-medium">{{ __('text.permissions') }}</label>

                <!-- Select All Checkbox -->
                <div class="mb-4">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" id="selectAll" wire:model.live="selectAll" class="form-checkbox rounded text-brand-500">
                        <span class="text-sm">{{ __('text.Select All / Unselect All') }}</span>
                    </label>
                </div>

                <!-- Permission Sections -->
                @foreach($groupedPermissions as $sectionName => $permissions)
                    @if(count($permissions) > 0)
                        <div class="mb-6">
                            <h3 class="text-base font-semibold mb-3 text-gray-900 dark:text-white">{{ $sectionName }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                @foreach($permissions as $permission)
                                    <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded">
                                        <input
                                            type="checkbox"
                                            value="{{ $permission['id'] }}"
                                            wire:model="permissions"
                                            class="form-checkbox rounded text-brand-500">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $permission['name'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">{{ __('text.Save') }}</button>
                <a href="{{ route('tenant.roles') }}"
                    class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-error-500 shadow-theme-xs hover:bg-error-600">
                    {{ __('text.Cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>