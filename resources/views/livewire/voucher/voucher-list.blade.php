<div class="p-4">
    <div class="space-y-6 max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Vouchers</h2>
            <a href="{{ route('tenant.vouchers.create') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                New Voucher
            </a>
        </div>

        @if (session()->has('message'))
            <div
                class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/30 dark:text-green-200">
                {{ session('message') }}
            </div>
        @endif

        <div class="flex items-center gap-4">
            <div class="flex-1">
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Search Voucher"
                       class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
        </div>

        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-950">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            S.No
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Voucher Name
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Voucher Code
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Valid From
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Valid To
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Discount (%)
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-900">
                    @forelse ($vouchers as $index => $voucher)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                {{ $vouchers->firstItem() + $index }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-900 dark:text-gray-100">
                                {{ $voucher->voucher_name }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                {{ $voucher->voucher_code }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                {{ $voucher->valid_from->format('d-m-Y') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                {{ $voucher->valid_to->format('d-m-Y') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                {{ $voucher->discount_percentage }}%
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right space-x-2">
                                <a href="{{ route('tenant.vouchers.edit', $voucher) }}"
                                   class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                                    Edit
                                </a>
                                <button type="button"
                                        wire:click="confirmDelete({{ $voucher->id }})"
                                        class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                No vouchers found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if ($vouchers->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $vouchers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('confirm-voucher-delete', ({ voucherId }) => {
            Swal.fire({
                title: 'Delete voucher?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('delete-voucher-confirmed', { voucherId });
                }
            });
        });
    });
</script>
