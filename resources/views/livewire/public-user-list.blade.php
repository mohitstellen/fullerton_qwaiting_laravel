<div class="p-4">
    <div class="space-y-6 max-w-7xl mx-auto">
        @if (session()->has('message'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/30 dark:text-green-200">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-900/30 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        <!-- Search Form -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">NRIC / FIN / Passport</label>
                    <input type="text" wire:model="searchNric" 
                           placeholder="NRIC / FIN / Passport"
                           class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Mobile Number</label>
                    <input type="text" wire:model="searchMobile" 
                           placeholder="Mobile Number"
                           class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Name</label>
                    <input type="text" wire:model="searchName" 
                           placeholder="Name"
                           class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Email</label>
                    <input type="text" wire:model="searchEmail" 
                           placeholder="Email"
                           class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Company</label>
                    <input type="text" wire:model="searchCompany" 
                           placeholder="Company"
                           class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                </div>
                <div class="flex gap-2 flex-shrink-0">
                    <button type="button" wire:click="search"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 h-11 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 whitespace-nowrap">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Search
                    </button>
                    <button type="button" wire:click="clearSearch"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 h-11 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900 whitespace-nowrap">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Add User Button -->
        <div class="flex justify-end">
            <a href="{{ route('tenant.public-user.create') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add User
            </a>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <button wire:click="switchTab('active')"
                        class="{{ $activeTab === 'active' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Active Users
                </button>
                <button wire:click="switchTab('inactive')"
                        class="{{ $activeTab === 'inactive' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    Non Active Users
                </button>
            </nav>
        </div>

        <!-- User List Table -->
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <!-- Pagination above table -->
            @if($members->total() > 25)
            <div class="mb-4 flex items-center justify-between flex-wrap gap-3 p-4">
                <div class="flex items-center gap-0">
                    @if($members->onFirstPage())
                        <button disabled class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-l-md cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">First</button>
                    @else
                        <button wire:click="gotoPage(1)" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">First</button>
                    @endif
                    
                    @if($members->onFirstPage())
                        <button disabled class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-white border-t border-b border-r border-gray-300 cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">Previous</button>
                    @else
                        <button wire:click="previousPage" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">Previous</button>
                    @endif

                    @php
                        $currentPage = $members->currentPage();
                        $lastPage = $members->lastPage();
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

                    @if($members->hasMorePages())
                        <button wire:click="nextPage" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">Next</button>
                    @else
                        <button disabled class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-white border-t border-b border-r border-gray-300 cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">Next</button>
                    @endif

                    @if($members->hasMorePages())
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
                    <thead class="border-b border-gray-100 dark:border-gray-200 text-gray-800 dark:text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-white">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-white">NRIC / FIN / Passport</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-white">DOB</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-white">Gender</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-white">Mobile Number</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-white">Email</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-white">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-900">
                        @forelse ($members as $member)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-900 dark:text-gray-100">
                                    {{ $member->full_name }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $member->display_nric_fin ?? $member->nric_fin }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $member->date_of_birth ? $member->date_of_birth->format('d/m/Y') : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $member->gender ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                    +{{ $member->mobile_country_code }} {{ $member->mobile_number }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $member->email }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('tenant.public-user.edit', $member->id) }}"
                                       class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-indigo-700"
                                       title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <button type="button" 
                                            wire:click="sendMemberEmail({{ $member->id }})"
                                            class="inline-flex items-center rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-green-700"
                                            title="Send Email">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No {{ $activeTab === 'active' ? 'active' : 'inactive' }} members found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination below table -->
            @if($members->total() > 25)
            <div class="mt-4 flex items-center justify-between flex-wrap gap-3 p-4">
                <div class="flex items-center gap-0">
                    @if($members->onFirstPage())
                        <button disabled class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-l-md cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">First</button>
                    @else
                        <button wire:click="gotoPage(1)" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">First</button>
                    @endif
                    
                    @if($members->onFirstPage())
                        <button disabled class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-white border-t border-b border-r border-gray-300 cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">Previous</button>
                    @else
                        <button wire:click="previousPage" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">Previous</button>
                    @endif

                    @php
                        $currentPage = $members->currentPage();
                        $lastPage = $members->lastPage();
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

                    @if($members->hasMorePages())
                        <button wire:click="nextPage" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border-t border-b border-r border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">Next</button>
                    @else
                        <button disabled class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-white border-t border-b border-r border-gray-300 cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500">Next</button>
                    @endif

                    @if($members->hasMorePages())
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

