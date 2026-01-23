<div class="space-y-4">

    <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Account Manager</label>
        <div class="relative mt-1">
            <input type="text"
                wire:model.live="accountManagerSearch"
                x-on:click="open = true"
                x-on:focus="open = true"
                placeholder="Search account manager..."
                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 pr-10 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                autocomplete="off">
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Company Name *</label>
            <input type="text"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                wire:model.defer="company.company_name">
            @error('company.company_name')
            <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div x-show="open && $wire.accountManagerSearch.length > 0"
            x-cloak
            class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-300 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
            @forelse ($this->filteredAccountManagers as $manager)
            <button type="button"
                wire:click="selectAccountManager({{ $manager['id'] }}, '{{ $manager['name'] }}')"
                x-on:click="open = false"
                class="w-full px-4 py-2 text-left text-sm text-gray-900 hover:bg-blue-50 dark:text-gray-100 dark:hover:bg-gray-700">
                <div class="font-medium">{{ $manager['name'] }}</div>
                @if(!empty($manager['email']))
                <div class="text-xs text-gray-500">{{ $manager['email'] }}</div>
                @endif
            </button>
            @empty
            <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                No account managers found
            </div>
            @endforelse
        </div>

        <input type="hidden" wire:model="company.account_manager_id">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Address</label>
        <textarea rows="2"
            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            wire:model="company.address"></textarea>
        @error('company.address')
        <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Billing Code *</label>
        <input type="text"
            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            wire:model.defer="company.billing_code">
        @error('company.billing_code')
        <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Remarks</label>
        <textarea rows="2"
            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            wire:model.defer="company.remarks"></textarea>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Status</label>
            <select
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                wire:model.defer="company.status">
                <option value="active">{{ __('text.Active') }}</option>
                <option value="expired">{{ __('text.Expired') }}</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">No. of EHS appointments /
                year</label>
            <input type="number" min="1"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                wire:model.defer="company.ehs_appointments_per_year">
            @error('company.ehs_appointments_per_year')
            <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="border-t border-gray-200 pt-4 dark:border-gray-700 contact-section">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Primary Contact</h3>
        <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Name</label>
                <input type="text"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    wire:model.defer="company.contact_person1_name">
                @error('company.contact_person1_name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Mobile No</label>
                <input type="text"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    wire:model.defer="company.contact_person1_phone">
                @error('company.contact_person1_phone')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email ID</label>
                <input type="email"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    wire:model.defer="company.contact_person1_email">
                @error('company.contact_person1_email')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>


</div>