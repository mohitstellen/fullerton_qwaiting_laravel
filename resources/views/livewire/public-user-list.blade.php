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
                                            wire:click="sendEmail({{ $member->id }})"
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
            
            <!-- Pagination -->
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Showing <span class="font-medium">{{ $members->firstItem() ?? 0 }}</span> 
                        to <span class="font-medium">{{ $members->lastItem() ?? 0 }}</span> 
                        of <span class="font-medium">{{ $members->total() }}</span> results
                    </div>
                    @if ($members->hasPages())
                        <div>
                            {{ $members->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

