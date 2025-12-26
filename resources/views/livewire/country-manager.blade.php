<div class="p-4">
    <h2 class="text-xl font-semibold mb-4">{{ __('setting.Country Master') }}</h2>

    <div class="mb-4">
        <div class="mb-4 flex justify-between mb-4 gap-3 flex-wrap">
            <div class="flex flex-wrap gap-3 flex-1">
                <!-- Search by Code -->
                <div class="relative w-full lg:w-[200px]">
                    <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/4">
                        <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""></path>
                        </svg>
                    </span>
                    <input type="text"
                        wire:model.live.debounce.300ms="searchCode"
                        placeholder="{{ __('setting.Search Code') }}..."
                        class="bg-white dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                </div>
                <!-- Search by Country Name -->
                <div class="relative w-full lg:w-[200px]">
                    <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/4">
                        <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""></path>
                        </svg>
                    </span>
                    <input type="text"
                        wire:model.live.debounce.300ms="searchCountryName"
                        placeholder="{{ __('setting.Search Country') }}..."
                        class="bg-white dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                </div>
            </div>

            <div class="flex gap-x-3">
                <button wire:click="exportCSV" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors rounded-lg bg-green-600 hover:bg-green-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    {{ __('setting.Export to CSV') }}
                </button>
                <button wire:click="openAddModal" class="p-3 text-sm font-medium text-white transition-colors rounded-lg bg-brand-500 hover:bg-brand-600">
                    <i class="ri-add-circle-line"></i> {{ __('text.Add') }}
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg p-4 shadow border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>

                    <tr class="border-b border-gray-300">
                        <th class="px-5 py-3 sm:px-6">
                            Country (Code)
                        </th>

                        <th class="px-5 py-3 sm:px-6">
                            Country Code
                        </th>

                        <th class="px-5 py-3 sm:px-6">
                            Mobile Length
                        </th>

                        <th class="px-5 py-3 sm:px-6">
                            Created
                        </th>

                        <th class="px-5 py-3 sm:px-6">

                            {{ __('setting.Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="dark:divide-gray-800">
                    @php $dateformat = Auth::user()->date_format ?? 'd M Y'; @endphp
                    @if(count($countries) > 0)
                    @foreach ($countries as $country)
                    <tr>
                        <td class="px-5 py-3 sm:px-6 border-b border-gray-300">{{ $country->name }} (+{{ $country->phone_code }})</td>
                        <td class="px-5 py-3 sm:px-6 border-b border-gray-300">{{ $country->country_code ?? '-' }}</td>
                        <td class="px-5 py-3 sm:px-6 border-b border-gray-300">{{ $country->mobile_length ?? '-' }}</td>
                        <td class="px-5 py-3 sm:px-6 border-b border-gray-300">{{ $country->created_at->format($dateformat) }}</td>
                        <td class="p-3 border-b border-gray-300">
                            <div
                                x-data="{
            open: false,
            top: 0,
            left: 0
        }"
                                class="inline-block">

                                <!-- Three dots button -->
                                <button
                                    @click="
                const rect = $el.getBoundingClientRect();
                top = rect.bottom + 8;
                left = rect.right - 192;
                open = !open;
            "
                                    class="text-gray-500 hover:text-gray-700 focus:outline-none">

                                    <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.74902 11.0285 19.74902 11.995V12.005C19.74902 12.9715 18.9655 13.755 17.99902 13.755C17.03253 13.755 16.24902 12.9715 16.24902 12.005V11.995C16.24902 11.0285 17.03253 10.245 17.99902 10.245ZM13.74902 11.995C13.74902 11.0285 12.96552 10.245 11.99902 10.245C11.03253 10.245 10.24902 11.0285 10.24902 11.995V12.005C10.24902 12.9715 11.03253 13.755 11.99902 13.755C12.96552 13.755 13.74902 12.9715 13.74902 12.005V11.995Z" />
                                    </svg>
                                </button>

                                <!-- Dropdown -->
                                <div
                                    x-show="open"
                                    x-transition
                                    @click.outside="open = false"
                                    :style="{ top: top + 'px', left: left + 'px' }"
                                    class="fixed z-[9999]
                   w-48 rounded-xl bg-white p-2
                   border border-gray-200 shadow-xl">

                                    <button
                                        wire:click="viewLogs({{ $country->id }})"
                                        class="block w-full rounded-lg px-4 py-2 text-left text-sm
                       text-gray-600 hover:bg-gray-100">
                                        View Logs
                                    </button>

                                    <button
                                        wire:click="openEditModal({{ $country->id }})"
                                        class="block w-full rounded-lg px-4 py-2 text-left text-sm
                       text-gray-600 hover:bg-gray-100">
                                        Edit
                                    </button>

                                    <button
                                        wire:click="confirmDelete({{ $country->id }})"
                                        class="block w-full rounded-lg px-4 py-2 text-left text-sm
                       text-red-600 hover:bg-red-50">
                                        Delete
                                    </button>
                                </div>

                            </div>
                        </td>
                    </tr>

                    @endforeach
                    @else
                    <tr>
                        <td colspan="12" class="text-center py-6">
                            <img src="{{ url('images/no-record.jpg') }}" alt="{{ __('setting.No Records Found here') }}"
                                class="mx-auto" style="max-width: 300px">
                           
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{ $countries->links() }}

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