<div class="p-4">
    <div class="space-y-6 max-w-3xl mx-auto">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">New Voucher</h2>
            <a href="{{ route('tenant.vouchers.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                Back to list
            </a>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <form wire:submit.prevent="save" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Voucher Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           placeholder="Voucher Name"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                           wire:model.defer="voucher.voucher_name">
                    @error('voucher.voucher_name')
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Voucher Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           placeholder="Voucher Code"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                           wire:model.defer="voucher.voucher_code">
                    @error('voucher.voucher_code')
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Valid From <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                               wire:model.defer="voucher.valid_from" onclick="this.showPicker()">
                        @error('voucher.valid_from')
                            <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            Valid To <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                               wire:model.defer="voucher.valid_to" onclick="this.showPicker()">
                        @error('voucher.valid_to')
                            <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Discount (%) <span class="text-red-500">*</span>
                    </label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           max="100"
                           placeholder="Discount (%)"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                           wire:model.defer="voucher.discount_percentage">
                    @error('voucher.discount_percentage')
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        No. of Redemption
                    </label>
                    <input type="number"
                           min="1"
                           placeholder="No.of Redemption"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                           wire:model.defer="voucher.no_of_redemption">
                    @error('voucher.no_of_redemption')
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button"
                            onclick="window.location.href='{{ route('tenant.vouchers.index') }}'"
                            class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                        Cancel
                    </button>
                    <button type="submit"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                        Save Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
