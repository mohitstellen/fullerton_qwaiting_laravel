<div class="p-4">
    <div class="space-y-6 max-w-7xl mx-auto">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Companies</h2>
        <a href="{{ route('tenant.companies.create') }}"
           class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
            Add Company
        </a>
    </div>

    @if (session()->has('message'))
        <div
            class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/30 dark:text-green-200">
            {{ session('message') }}
        </div>
    @endif

    <div
        class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-950">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        #
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Company Name
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Account Manager
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Contact Person
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Remarks
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Status
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-900">
                @forelse ($companies as $company)
                    <tr>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                            {{ $company->id }}
                        </td>
                       
                        <td class="whitespace-nowrap px-4 py-3 text-gray-900 dark:text-gray-100">
                            {{ $company->company_name }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-gray-900 dark:text-gray-100">
                            {{ $company->accountManager->name ?? 'N/A' }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-gray-900 dark:text-gray-100">
                            {{ $company->contact_person1_name }}
                        </td>
                       
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                            {{ Str::limit($company->remarks, 50) }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-900 dark:text-gray-100">
                            {{ $company->status == 'active' ? __('setting.Active') : __('setting.Expired') }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right space-x-2">
                            <a href="{{ route('tenant.companies.edit', $company) }}"
                               class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                                Edit
                            </a>
                            <button type="button"
                                    wire:click="confirmDelete({{ $company->id }})"
                                    class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            No companies found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('confirm-company-delete', ({ companyId }) => {
            Swal.fire({
                title: 'Delete company?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('delete-company-confirmed', { companyId });
                }
            });
        });
    });
</script>