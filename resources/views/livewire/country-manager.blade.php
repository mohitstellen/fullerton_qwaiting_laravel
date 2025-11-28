<div class="p-4">
    <div class="flex justify-between">
        <h2 class="text-xl font-semibold dark:text-white/90 mb-4">{{ __('text.Country Code') }}</h2>
    </div>

    <div class="flex justify-end mb-4">
        <button wire:click="openAddModal" class="primary-btn py-2 px-3 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2"><i class="ri-add-circle-line"></i> {{ __('text.Add') }}
        </button>
    </div>

    <div class="p-4 md:p-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow">
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-auto">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-2 py-1">S.No</th>
                        <th class="border px-2 py-1">Country (Code)</th>
                        <th class="border px-2 py-1">Country Code</th>
                        <th class="border px-2 py-1">Mobile Length</th>
                        <th class="border px-2 py-1">Created</th>
                        <th class="border px-2 py-1">{{ __('text.Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @php $dateformat = Auth::user()->date_format ?? 'd M Y'; @endphp

                    @forelse($countries as $index => $country)
                        <tr>
                            {{-- Serial number with pagination --}}
                            <td class="border px-2 py-1">
                                {{ ($countries->currentPage() - 1) * $countries->perPage() + $index + 1 }}
                            </td>
                            <td class="border px-2 py-1">{{ $country->name }} (+{{ $country->phone_code }})</td>
                            <td class="border px-2 py-1">{{ $country->country_code ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $country->mobile_length ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $country->created_at->format($dateformat) }}</td>
                            <td class="border px-2 py-1">
                                <div class="flex items-center gap-2">
                                    <button wire:click="viewLogs({{ $country->id }})" 
                                            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors flex items-center justify-center" 
                                            title="View Logs"
                                            type="button">
                                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="openEditModal({{ $country->id }})" 
                                            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors" 
                                            title="Edit"
                                            type="button">
                                        <i class="ri-edit-line text-lg"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $country->id }})" 
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors" 
                                            title="Delete"
                                            type="button">
                                        <i class="ri-delete-bin-line text-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="text-center py-6">
                                <p><strong>{{ __('report.No records found.') }}</strong></p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $countries->links() }}
        </div>
    </div>

    {{-- Activity Logs Modal --}}
    @if ($showLogsModal && $selectedCountryForLogs)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center" style="padding-left: 290px;">
            <div class="bg-white p-6 rounded w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col mx-auto">
                <h2 class="text-lg font-bold mb-4">Country Audit Details</h2>
                
                <div class="overflow-y-auto flex-1">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="border px-2 py-1">S.No</th>
                                    <th class="border px-2 py-1">Country Name</th>
                                    <th class="border px-2 py-1">Country Code</th>
                                    <th class="border px-2 py-1">MobileNoLength</th>
                                    <th class="border px-2 py-1">Created By</th>
                                    <th class="border px-2 py-1">Created At</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($activityLogs as $index => $log)
                                    <tr>
                                        <td class="border px-2 py-1">{{ $index + 1 }}</td>
                                        <td class="border px-2 py-1">{{ $selectedCountryForLogs->name }}</td>
                                        <td class="border px-2 py-1">{{ $selectedCountryForLogs->country_code ?? '-' }}</td>
                                        <td class="border px-2 py-1">{{ $selectedCountryForLogs->mobile_length ?? '-' }}</td>
                                        <td class="border px-2 py-1">{{ $log->createdBy->name ?? 'N/A' }}</td>
                                        <td class="border px-2 py-1">{{ $log->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="border px-2 py-4 text-center">
                                            <p><strong>No activity logs found for this country.</strong></p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('showLogsModal', false)" class="bg-gray-300 px-3 py-2 rounded">Close</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Add/Edit Modal --}}
    @if ($showcountryModel)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded w-96">
                <h2 class="text-lg font-bold mb-4">{{ $countryId ? 'Edit Country Code' : 'Add Country Code' }}</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">
                        Country <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="select_countryId" class="w-full border rounded px-2 py-1 dark:bg-dark-900 dark:text-white/90 dark:border-gray-700">
                        <option value="">-- Select Country --</option>
                        @foreach ($allcountries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }} (+{{ $country->phonecode }})</option>
                        @endforeach
                    </select>
                    @error('select_countryId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">
                        Country Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="countryCode" 
                           class="w-full border rounded px-2 py-1 dark:bg-dark-900 dark:text-white/90 dark:border-gray-700" 
                           placeholder="Enter country code" />
                    @error('countryCode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">
                        Mobile Length <span class="text-red-500">*</span>
                    </label>
                    <input type="number" wire:model="mobileLength" 
                           class="w-full border rounded px-2 py-1 dark:bg-dark-900 dark:text-white/90 dark:border-gray-700" 
                           placeholder="Enter mobile length" min="1" max="20" />
                    @error('mobileLength') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showcountryModel', false)" class="bg-gray-300 px-3 py-2 rounded">Cancel</button>
                    <button wire:click="save" class="bg-blue-600 text-white px-3 py-2 rounded">Save</button>
                </div>
            </div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Livewire.on('alert', ({
            type,
            message
        }) => {
            Swal.fire({
                icon: type,
                text: message,
                timer: 2000,
                showConfirmButton: false
            });
        });

        Livewire.on('confirmDelete', () => {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the country code!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('delete');
                }
            });
        });
    });
</script>
