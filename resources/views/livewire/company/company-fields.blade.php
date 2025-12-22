<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Company Name *</label>
        <input type="text"
               class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
               wire:model.defer="company.company_name">
        @error('company.company_name')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Address</label>
        <textarea rows="2"
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                  wire:model.defer="company.address"></textarea>
        @error('company.address')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Billing Address</label>
        <textarea rows="2"
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                  wire:model.defer="company.billing_address"></textarea>
        @error('company.billing_address')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
        <label class="mt-2 inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
            <input type="checkbox"
                   class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                   wire:model.live="company.is_billing_same_as_company">
            <span>Billing address same as company address</span>
        </label>
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
</div>

