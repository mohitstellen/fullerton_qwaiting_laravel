<div class="p-4">
    <div class="space-y-6 max-w-7xl mx-auto">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Companies</h2>
        <div class="flex items-center gap-3 flex-wrap">
            <!-- Search Box -->
            <div class="relative w-full sm:w-[300px]">
                <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/4">
                    <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""></path>
                    </svg>
                </span>
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Search companies..." 
                       class="dark:bg-dark-900 bg-white shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
            </div>
            <div class="flex items-center gap-3">
                <button wire:click="exportCSV" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white shadow-sm rounded-lg bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export to CSV
                </button>
                <a href="{{ route('tenant.companies.create') }}"
                   class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                    Add Company
                </a>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
        <div
            class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/30 dark:text-green-200">
            {{ session('message') }}
        </div>
    @endif

    <div
        class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <!-- Pagination above table -->
        @if($companies->total() > 25)
        <div class="mb-4 flex items-center justify-between flex-wrap gap-3 p-4">
            <div class="flex items-center gap-0">
                @if($companies->onFirstPage())
                    <button disabled class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-l-md cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">First</button>
                @else
                    <button wire:click="gotoPage(1)" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">First</button>
                @endif
                
                @if($companies->onFirstPage())
                    <button disabled class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-white border-t border-b border-r border-gray-300 cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">Previous</button>
                @else
                    <button wire:click="previousPage" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">Previous</button>
                @endif

                @php
                    $currentPage = $companies->currentPage();
                    $lastPage = $companies->lastPage();
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($lastPage, $currentPage + 2);
                @endphp

                @if($startPage > 1)
                    <button wire:click="gotoPage(1)" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">1</button>
                    @if($startPage > 2)
                        <span class="px-2 py-1.5 text-sm text-gray-500 bg-white border-t border-b border-r border-gray-300 dark:bg-gray-800 dark:border-gray-700">...</span>
                    @endif
                @endif

                @for($page = $startPage; $page <= $endPage; $page++)
                    @if($page == $currentPage)
                        <button class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 border-t border-b border-r border-blue-600 dark:bg-blue-500 dark:border-blue-500">{{ $page }}</button>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">{{ $page }}</button>
                    @endif
                @endfor

                @if($endPage < $lastPage)
                    @if($endPage < $lastPage - 1)
                        <span class="px-2 py-1.5 text-sm text-gray-500 bg-white border-t border-b border-r border-gray-300 dark:bg-gray-800 dark:border-gray-700">...</span>
                    @endif
                    <button wire:click="gotoPage({{ $lastPage }})" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">{{ $lastPage }}</button>
                @endif

                @if($companies->hasMorePages())
                    <button wire:click="nextPage" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">Next</button>
                @else
                    <button disabled class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-white border-t border-b border-r border-gray-300 cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">Next</button>
                @endif

                @if($companies->hasMorePages())
                    <button wire:click="gotoPage({{ $lastPage }})" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">Last</button>
                @else
                    <button disabled class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-r-md cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">Last</button>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <select wire:model.live="perPage"
                    class="bg-white dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[36px] w-28 rounded-lg border border-gray-300 py-1.5 px-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="75">75</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        @endif
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
        <!-- Pagination below table -->
        @if($companies->total() > 25)
        <div class="mt-4 flex items-center justify-between flex-wrap gap-3 p-4">
            <div class="flex items-center gap-0">
                @if($companies->onFirstPage())
                    <button disabled class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-l-md cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">First</button>
                @else
                    <button wire:click="gotoPage(1)" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">First</button>
                @endif
                
                @if($companies->onFirstPage())
                    <button disabled class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-white border-t border-b border-r border-gray-300 cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">Previous</button>
                @else
                    <button wire:click="previousPage" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">Previous</button>
                @endif

                @php
                    $currentPage = $companies->currentPage();
                    $lastPage = $companies->lastPage();
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($lastPage, $currentPage + 2);
                @endphp

                @if($startPage > 1)
                    <button wire:click="gotoPage(1)" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">1</button>
                    @if($startPage > 2)
                        <span class="px-2 py-1.5 text-sm text-gray-500 bg-white border-t border-b border-r border-gray-300 dark:bg-gray-800 dark:border-gray-700">...</span>
                    @endif
                @endif

                @for($page = $startPage; $page <= $endPage; $page++)
                    @if($page == $currentPage)
                        <button class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 border-t border-b border-r border-blue-600 dark:bg-blue-500 dark:border-blue-500">{{ $page }}</button>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">{{ $page }}</button>
                    @endif
                @endfor

                @if($endPage < $lastPage)
                    @if($endPage < $lastPage - 1)
                        <span class="px-2 py-1.5 text-sm text-gray-500 bg-white border-t border-b border-r border-gray-300 dark:bg-gray-800 dark:border-gray-700">...</span>
                    @endif
                    <button wire:click="gotoPage({{ $lastPage }})" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">{{ $lastPage }}</button>
                @endif

                @if($companies->hasMorePages())
                    <button wire:click="nextPage" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">Next</button>
                @else
                    <button disabled class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-white border-t border-b border-r border-gray-300 cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">Next</button>
                @endif

                @if($companies->hasMorePages())
                    <button wire:click="gotoPage({{ $lastPage }})" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">Last</button>
                @else
                    <button disabled class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-r-md cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">Last</button>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <select wire:model.live="perPage"
                    class="bg-white dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[36px] w-28 rounded-lg border border-gray-300 py-1.5 px-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="75">75</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        @endif
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