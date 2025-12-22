<div class="p-4">
    <div class="space-y-6 max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Import Member Details</h2>
        </div>

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

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <button
                    wire:click="switchTab('import')"
                    class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors {{ $activeTab === 'import' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    Import Member Details
                </button>
                <a
                    href="{{ route('tenant.import-member-details.download-template') }}"
                    class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    Download Template
                </a>
            </nav>
        </div>

        <!-- Import Tab Content -->
        @if($activeTab === 'import')
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <form wire:submit.prevent="import" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Import Type -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Import Type
                        </label>
                        <select disabled class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            <option>Member Details</option>
                        </select>
                    </div>

                    <!-- Enforce password change on first login -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Enforce password change on first login
                        </label>
                        <select wire:model="enforcePasswordChange" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                        @error('enforcePasswordChange') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Company Name Search -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Company Name <span class="text-red-500">*</span>
                    </label>
                    <div x-data="{ 
                        showDropdown: @entangle('showCompanyDropdown'),
                        isFocused: false
                    }"
                        class="relative z-20 bg-transparent"
                        @click.away="showDropdown = false">
                        <div class="relative">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="companySearch"
                                @focus="showDropdown = true; isFocused = true"
                                @keydown.escape="showDropdown = false"
                                placeholder="Company Name"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                           
                        </div>

                        @if($showCompanyDropdown && count($allCompanies) > 0)
                        <div class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-auto">
                            @foreach($allCompanies as $company)
                            <div
                                wire:click="selectCompany({{ $company->id }}, {{ json_encode($company->company_name) }})"
                                class="px-4 py-2.5 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm text-gray-800 dark:text-white/90 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                                {{ $company->company_name }}
                            </div>
                            @endforeach
                        </div>
                        @elseif($showCompanyDropdown && strlen($companySearch) >= 1 && count($allCompanies) == 0)
                        <div class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg">
                            <div class="px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400">
                                No companies found
                            </div>
                        </div>
                        @endif
                    </div>
                    @error('company_id') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- File Upload -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Choose File
                    </label>
                    <div class="flex items-center gap-4 justify-between">
                        <div class="flex items-center gap-4 flex-1">
                            <label class="cursor-pointer">
                                <input type="file" wire:model="file" accept=".xlsx,.csv" class="hidden" />
                                <span class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                    Choose file
                                </span>
                            </label>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                @if($file)
                                    {{ $file->getClientOriginalName() }}
                                @else
                                    No file chosen
                                @endif
                            </span>
                        </div>
                        <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                            Import Member Details
                        </button>
                    </div>
                    @error('file') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>
            </form>
        </div>
        @endif

        <!-- Import History Table -->
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-950">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                S.No
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                File Name
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Company
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Created By
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Created Date time
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Imported Date Time
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-900">
                        @forelse ($imports as $index => $import)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $imports->firstItem() + $index }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-900 dark:text-gray-100">
                                    {{ $import->file_name }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $import->company->company_name ?? 'N/A' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $import->created_by }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $import->created_date_time->format('d/m/Y h:i A') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $import->imported_date_time ? $import->imported_date_time->format('d/m/Y h:i A') : 'N/A' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    @if($import->status == 1)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-200">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                            </svg>
                                            In-Progress
                                        </span>
                                    @elseif($import->status == 2)
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200">
                                            Completed
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200">
                                            Failed
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No import records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $imports->links() }}
            </div>
        </div>
    </div>
</div>
