<div class="p-4">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Edit Company</h2>
            <a href="{{ route('tenant.companies.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                Back to list
            </a>
        </div>

        {{-- Two Column Layout --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
            {{-- Left Column: Company Form + Appointment Type Validity --}}
            <div class="space-y-6 lg:col-span-2">
                {{-- Company Form --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <form wire:submit.prevent="update" class="space-y-6">
                        <div class="edit-company-form">
                            @include('livewire.company.company-form')
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <button type="submit"
                                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                                Update
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Appointment Type Validity Card --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <div class="space-y-4">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Appointment Type Validity</h3>
                            </div>
                    <button type="button"
                            wire:click="createAppointmentType"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                        Add
                    </button>
                </div>

                @if (session()->has('appointmentTypesMessage'))
                    <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900 dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-100">
                        {{ session('appointmentTypesMessage') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    S.No
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Appointment Type
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Valid From - To
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Applicable For
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            @forelse ($companyAppointmentTypes as $index => $appointmentType)
                                <tr wire:key="appointment-type-{{ $appointmentType->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $companyAppointmentTypes->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $appointmentType->appointmentType->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $appointmentType->valid_from->format('d-M-Y') }} - {{ $appointmentType->valid_to->format('d-M-Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $appointmentType->applicable_for }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button"
                                                    wire:click="editAppointmentType({{ $appointmentType->id }})"
                                                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 p-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800"
                                                    title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button type="button"
                                                    wire:click="deleteAppointmentType({{ $appointmentType->id }})"
                                                    class="inline-flex items-center justify-center rounded-lg border border-red-200 p-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900/40 dark:text-red-300 dark:hover:bg-red-900/20"
                                                    title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this appointment type validity?')">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No appointment type validities have been added yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination for Appointment Type Validity --}}
                <div class="mt-4">
                    {{ $companyAppointmentTypes->links() }}
                </div>

                {{-- Appointment Type Form Modal --}}
                @if ($showAppointmentTypeForm)
                    <div class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999" style="background: rgba(0,0,0,0.25);">
                        <div id="appointment-type-modal" class="p-5 modal-close-btn inset-0 m-auto w-full max-w-lg bg-white-400/50 bg-white rounded-xl relative dark:bg-gray-800 dark:border-gray-700 shadow-xl">
                            <button wire:click="closeAppointmentTypeForm" class="absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-500 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11">
                                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
                                </svg>
                            </button>

                            <div class="mb-4">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                    {{ $appointmentTypeForm['id'] ? 'Edit Appointment Type Validity' : 'Add Appointment Type Validity' }}
                                </h3>
                            </div>

                            <div class="overflow-y-auto" style="max-height: calc(100vh - 200px);">
                                <form wire:submit.prevent="saveAppointmentType" class="space-y-4">
                                    <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Appointment Type *</label>
                                        <div class="relative mt-1">
                                            <input type="text"
                                                   wire:model.live="appointmentTypeSearch"
                                                   x-on:click="open = true"
                                                   x-on:focus="open = true"
                                                   placeholder="Type to search appointment type..."
                                                   class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 pr-10 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                   autocomplete="off">
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        
                                        <div x-show="open && $wire.appointmentTypeSearch.length > 0"
                                             x-cloak
                                             class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-300 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                                            @forelse ($this->filteredAppointmentTypes as $type)
                                                <button type="button"
                                                        wire:click="selectAppointmentType({{ $type['id'] }}, '{{ $type['name'] }}')"
                                                        x-on:click="open = false"
                                                        class="w-full px-4 py-2 text-left text-sm text-gray-900 hover:bg-blue-50 dark:text-gray-100 dark:hover:bg-gray-700">
                                                    {{ $type['name'] }}
                                                </button>
                                            @empty
                                                <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                                                    No appointment types found
                                                </div>
                                            @endforelse
                                        </div>
                                        
                                        <input type="hidden" wire:model="appointmentTypeForm.appointment_type_id">
                                        @error('appointmentTypeForm.appointment_type_id')
                                            <span class="text-sm text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Valid From *</label>
                                            <input type="date"
                                                   wire:model.defer="appointmentTypeForm.valid_from"
                                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"  onclick="this.showPicker()">
                                            @error('appointmentTypeForm.valid_from')
                                                <span class="text-sm text-red-500">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Valid To *</label>
                                            <input type="date"
                                                   wire:model.defer="appointmentTypeForm.valid_to"
                                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" onclick="this.showPicker()">
                                            @error('appointmentTypeForm.valid_to')
                                                <span class="text-sm text-red-500">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Applicable For *</label>
                                        <select id="appointment-type-applicable-for-select"
                                                wire:model.defer="appointmentTypeForm.applicable_for"
                                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                data-no-select2="true">
                                            @foreach ($applicableForOptions as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                        @error('appointmentTypeForm.applicable_for')
                                            <span class="text-sm text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="flex items-center justify-end gap-3 pt-2">
                                        <button type="button"
                                                wire:click="closeAppointmentTypeForm"
                                                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                                            Clear
                                        </button>
                                        <button type="submit"
                                                class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                                            {{ $appointmentTypeForm['id'] ? 'Update' : 'Add' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
                    </div>
                </div>
            </div>

            {{-- Right Column: Contact Details + Company Packages --}}
            <div class="space-y-6 lg:col-span-3">
                {{-- Contact Details Section --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Contact Details</h3>
                    
                    <div class="space-y-6">
                        {{-- Primary Contact --}}
                        <div>
                            <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Primary Contact</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Name</label>
                                    <input type="text"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                           wire:model.defer="company.contact_person1_name">
                                    @error('company.contact_person1_name')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Mobile No</label>
                                    <input type="text"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                           wire:model.defer="company.contact_person1_phone">
                                    @error('company.contact_person1_phone')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email ID</label>
                                    <input type="email"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                           wire:model.defer="company.contact_person1_email">
                                    @error('company.contact_person1_email')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Secondary Contact --}}
                        <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                            <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Secondary Contact</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Name</label>
                                    <input type="text"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                           wire:model.defer="company.contact_person2_name">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Mobile No</label>
                                    <input type="text"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                           wire:model.defer="company.contact_person2_phone">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email ID</label>
                                    <input type="email"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                           wire:model.defer="company.contact_person2_email">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Company Packages Section --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <div class="space-y-4">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Company packages</h3>
                            </div>
                    <button type="button"
                            wire:click="createMapping"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                        Add
                    </button>
                </div>

                @if (session()->has('companyPackagesMessage'))
                    <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900 dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-100">
                        {{ session('companyPackagesMessage') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    S.No
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Company Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Appointment Type
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Package Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Amount
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            @forelse ($companyPackages as $index => $mapping)
                                <tr wire:key="company-package-{{ $mapping->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $companyPackages->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $companyModel->company_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $mapping->appointmentType->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $mapping->package->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ number_format($mapping->package->amount ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button"
                                                    wire:click="editMapping({{ $mapping->id }})"
                                                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 p-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800"
                                                    title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button type="button"
                                                    wire:click="deleteMapping({{ $mapping->id }})"
                                                    class="inline-flex items-center justify-center rounded-lg border border-red-200 p-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900/40 dark:text-red-300 dark:hover:bg-red-900/20"
                                                    title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this package?')">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No company packages have been added yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination for Company Packages --}}
                <div class="mt-4">
                    {{ $companyPackages->links() }}
                </div>

                @if ($showForm)
                    <div class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999" style="background: rgba(0,0,0,0.25);">
                        <div id="company-packages-modal" class="p-5 modal-close-btn inset-0 m-auto w-full max-w-2xl bg-white-400/50 bg-white rounded-xl relative dark:bg-gray-800 dark:border-gray-700 shadow-xl">
                            <button wire:click="closeForm" class="absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-500 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11">
                                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" />
                                </svg>
                            </button>

                            <div class="mb-2">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                    {{ $mappingForm['id'] ? 'Edit company package' : 'Add company package' }}
                                </h3>
                            </div>

                            <div class="overflow-y-auto" style="height: calc(100% - 110px);">
                                <form wire:submit.prevent="saveMapping" class="space-y-5">
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Appointment type *</label>
                                            <div class="relative mt-1">
                                                <input type="text"
                                                       wire:model.live="packageAppointmentTypeSearch"
                                                       x-on:click="open = true"
                                                       x-on:focus="open = true"
                                                       placeholder="Type to search appointment type..."
                                                       class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 pr-10 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                       autocomplete="off">
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            
                                            <div x-show="open"
                                                 x-cloak
                                                 class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-300 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                                                @forelse ($this->filteredPackageAppointmentTypes as $type)
                                                    <button type="button"
                                                            wire:click="selectPackageAppointmentType({{ $type['id'] }}, '{{ $type['name'] }}')"
                                                            x-on:click="open = false"
                                                            class="w-full px-4 py-2 text-left text-sm text-gray-900 hover:bg-blue-50 dark:text-gray-100 dark:hover:bg-gray-700">
                                                        {{ $type['name'] }}
                                                    </button>
                                                @empty
                                                    <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                                                        @if($wire.packageAppointmentTypeSearch)
                                                            No appointment types found
                                                        @else
                                                            Type to search appointment types...
                                                        @endif
                                                    </div>
                                                @endforelse
                                            </div>
                                            
                                            <input type="hidden" wire:model="mappingForm.appointment_type_id">
                                            @error('mappingForm.appointment_type_id')
                                                <span class="text-sm text-red-500">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Package *</label>
                                            @if (empty($packageOptions))
                                                <div class="mt-1 rounded-lg border border-gray-300 bg-gray-50 px-3 py-4 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-400">
                                                    Select an appointment type to load packages.
                                                </div>
                                            @else
                                                <div class="mt-1" wire:ignore>
                                                    <select id="company-packages-select"
                                                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                            data-placeholder="Select packages..."
                                                            multiple>
                                                        @foreach ($packageOptions as $package)
                                                            <option value="{{ $package['id'] }}">
                                                                {{ $package['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Select one or more packages.</p>
                                            @endif
                                            @error('mappingForm.package_ids')
                                                <span class="text-sm text-red-500">{{ $message }}</span>
                                            @enderror
                                            @error('mappingForm.package_ids.*')
                                                <span class="text-sm text-red-500">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Modes of identification</label>
                                            <div class="mt-2" wire:ignore>
                                                <select id="company-moi-select"
                                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                        data-placeholder="Select MOI..."
                                                        multiple>
                                                    @foreach ($defaultMoiOptions as $option)
                                                        <option value="{{ $option }}">
                                                            {{ $option }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('mappingForm.modes_of_identification.*')
                                                <span class="text-sm text-red-500">{{ $message }}</span>
                                            @enderror
                                        </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Clinics</label>
                                        <div class="mt-1" wire:ignore>
                                            <select id="company-clinics-select"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                    multiple
                                                    data-placeholder="Select clinics">
                                                    @foreach ($locations as $location)
                                                        <option value="{{ $location['id'] }}">
                                                            {{ $location['name'] }}
                                                        </option>
                                                    @endforeach
                                            </select>
                                        </div>
                                        @error('mappingForm.clinic_ids.*')
                                            <span class="text-sm text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Remarks</label>
                                        <textarea rows="3"
                                                  wire:model.defer="mappingForm.remarks"
                                                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                                        @error('mappingForm.remarks')
                                            <span class="text-sm text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="flex items-center justify-end gap-3 pt-2">
                                        <button type="button"
                                                wire:click="closeForm"
                                                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                                class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                                            Save
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            /* Hide contact fields in edit form (they're shown in right column) */
            .edit-company-form .contact-section {
                display: none;
            }
            
            /* Fix Select2 clear button overlapping with dropdown arrow */
            .select2-container--default .select2-selection--multiple .select2-selection__clear {
                position: absolute;
                right: 30px;
                top: 30%;
                transform: translateY(-50%);
                z-index: 10;
                font-size: 18px;
                font-weight: bold;
                padding: 0 5px;
            }
            
            /* Ensure proper spacing for the dropdown arrow */
            .select2-container--default .select2-selection--multiple {
                padding-right: 60px !important;
                min-height: 38px;
            }
            
            /* Position the arrow properly - hide it since Select2 multi-select doesn't need it */
            .select2-container--default.select2-container--open .select2-selection--multiple .select2-selection__arrow {
                display: none;
            }
            
            /* Better spacing for selected items */
            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                margin: 2px 5px 2px 0;
            }
            
            /* Adjust the rendered choices container */
            .select2-container--default .select2-selection--multiple .select2-selection__rendered {
                padding-right: 50px;
            }
        </style>
    @endpush
    @push('scripts')
        <script>
            document.addEventListener('livewire:init', function() {
                let select2Initialized = false;
                
                function destroySelect2(selector) {
                    const $el = $(selector);
                    if ($el.length && $el.hasClass('select2-hidden-accessible')) {
                        try {
                            $el.select2('destroy');
                        } catch(e) {
                            // Ignore errors
                        }
                    }
                }
                
                // Watch for modal visibility and initialize Select2 when it becomes visible
                function checkAndInitializeSelect2() {
                    const modal = $('#company-packages-modal');
                    if (modal.length && modal.is(':visible') && !select2Initialized) {
                        const clinicsSelect = $('#company-clinics-select');
                        const moiSelect = $('#company-moi-select');
                        const packagesSelect = $('#company-packages-select');
                        
                        // Check if any Select2 needs initialization
                        if ((clinicsSelect.length && !clinicsSelect.hasClass('select2-hidden-accessible')) ||
                            (moiSelect.length && !moiSelect.hasClass('select2-hidden-accessible')) ||
                            (packagesSelect.length && !packagesSelect.hasClass('select2-hidden-accessible'))) {
                            initializeSelect2();
                            select2Initialized = true;
                        }
                    } else if (!modal.length || !modal.is(':visible')) {
                        // Modal is closed - destroy Select2 instances for clean reinitialization
                        if (select2Initialized) {
                            destroySelect2('#company-clinics-select');
                            destroySelect2('#company-moi-select');
                            destroySelect2('#company-packages-select');
                            select2Initialized = false;
                        }
                    }
                }
                
                // Check periodically for modal visibility
                setInterval(checkAndInitializeSelect2, 100);
                
                function initializeSelect2() {
                    const modal = $('#company-packages-modal');
                    if (!modal.length || !modal.is(':visible')) {
                        return;
                    }
                    
                    // Check if jQuery and Select2 are available
                    if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
                        console.error('jQuery or Select2 not loaded');
                        return;
                    }
                    
                    // Prevent Select2 from being applied to elements with data-no-select2 attribute
                    $(document).on('select2:init', function(e) {
                        if ($(e.target).attr('data-no-select2') === 'true') {
                            $(e.target).select2('destroy');
                        }
                    });
                    
                    // Prevent modal from closing when clicking on Select2 elements
                    $(document).off('click.select2-modal', '.select2-container, .select2-dropdown');
                    $(document).on('click.select2-modal', '.select2-container, .select2-dropdown', function(e) {
                        e.stopPropagation();
                    });
                    
                    // Initialize Packages Select2 (multi-select with search)
                    const packagesSelect = $('#company-packages-select');
                    if (packagesSelect.length && !packagesSelect.hasClass('select2-hidden-accessible')) {
                        const selectedPackages = @this.get('mappingForm.package_ids') || [];
                        const selectedPackagesStrings = selectedPackages.map(v => String(v));
                        
                        console.log('Initializing Packages Select2 with selected:', selectedPackagesStrings);
                        
                        packagesSelect.select2({
                            placeholder: 'Search and select packages',
                            allowClear: true,
                            width: '100%',
                            dropdownParent: modal,
                            closeOnSelect: false
                        });
                        
                        // Prevent dropdown from closing when clicking inside
                        packagesSelect.on('select2:open', function() {
                            $('.select2-dropdown').off('mousedown').on('mousedown', function(e) {
                                e.stopPropagation();
                            });
                        });
                        
                        // Set selected values BEFORE binding change event to avoid triggering Livewire
                        if (selectedPackagesStrings.length > 0) {
                            packagesSelect.val(selectedPackagesStrings);
                        }
                        
                        // Bind change event AFTER setting initial values
                        packagesSelect.on('change.select2', function(e) {
                            let data = $(this).val() || [];
                            @this.set('mappingForm.package_ids', data);
                        });
                        
                        // Trigger Select2 to update the UI display
                        if (selectedPackagesStrings.length > 0) {
                            setTimeout(function() {
                                packagesSelect.trigger('change.select2');
                            }, 50);
                        }
                    }
                    
                    // Initialize Clinics Select2 (multi-select with search)
                    const clinicsSelect = $('#company-clinics-select');
                    if (clinicsSelect.length && !clinicsSelect.hasClass('select2-hidden-accessible')) {
                        const selectedClinics = @this.get('mappingForm.clinic_ids') || [];
                        const selectedClinicsStrings = selectedClinics.map(v => String(v));
                        
                        console.log('Initializing Clinics Select2 with selected:', selectedClinicsStrings);
                        
                        clinicsSelect.select2({
                            placeholder: 'Search and select clinics',
                            allowClear: true,
                            width: '100%',
                            dropdownParent: modal,
                            closeOnSelect: false
                        });
                        
                        // Prevent dropdown from closing when clicking inside
                        clinicsSelect.on('select2:open', function() {
                            $('.select2-dropdown').off('mousedown').on('mousedown', function(e) {
                                e.stopPropagation();
                            });
                        });
                        
                        // Set selected values BEFORE binding change event to avoid triggering Livewire
                        if (selectedClinicsStrings.length > 0) {
                            clinicsSelect.val(selectedClinicsStrings);
                        }
                        
                        // Bind change event AFTER setting initial values
                        clinicsSelect.on('change.select2', function(e) {
                            let data = $(this).val() || [];
                            @this.set('mappingForm.clinic_ids', data);
                        });
                        
                        // Trigger Select2 to update the UI display
                        if (selectedClinicsStrings.length > 0) {
                            setTimeout(function() {
                                clinicsSelect.trigger('change.select2');
                            }, 50);
                        }
                    }
                    
                    // Initialize MOI Select2 (multi-select with search)
                    const moiSelect = $('#company-moi-select');
                    if (moiSelect.length && !moiSelect.hasClass('select2-hidden-accessible')) {
                        const selectedMoi = @this.get('mappingForm.modes_of_identification') || [];
                        const selectedMoiStrings = selectedMoi.map(v => String(v));
                        
                        console.log('Initializing MOI Select2 with selected:', selectedMoiStrings);
                        
                        moiSelect.select2({
                            placeholder: 'Search and select modes of identification',
                            allowClear: true,
                            width: '100%',
                            dropdownParent: modal,
                            closeOnSelect: false
                        });
                        
                        // Prevent dropdown from closing when clicking inside
                        moiSelect.on('select2:open', function() {
                            $('.select2-dropdown').off('mousedown').on('mousedown', function(e) {
                                e.stopPropagation();
                            });
                        });
                        
                        // Set selected values BEFORE binding change event to avoid triggering Livewire
                        if (selectedMoiStrings.length > 0) {
                            moiSelect.val(selectedMoiStrings);
                        }
                        
                        // Bind change event AFTER setting initial values
                        moiSelect.on('change.select2', function(e) {
                            let data = $(this).val() || [];
                            @this.set('mappingForm.modes_of_identification', data);
                        });
                        
                        // Trigger Select2 to update the UI display
                        if (selectedMoiStrings.length > 0) {
                            setTimeout(function() {
                                moiSelect.trigger('change.select2');
                            }, 50);
                        }
                    }
                }
                
                // Listen for Livewire events to set select values
                Livewire.on('company-packages:init-clinic-select', (event) => {
                    setTimeout(function() {
                        const select = $('#company-clinics-select');
                        if (select.length) {
                            const selected = (event.selected || []).map(v => String(v));
                            if (select.hasClass('select2-hidden-accessible')) {
                                // Select2 already initialized, just update values
                                select.val(selected).trigger('change');
                            } else {
                                // Select2 not initialized yet, initialize it first
                                initializeSelect2();
                                setTimeout(function() {
                                    if (select.hasClass('select2-hidden-accessible')) {
                                        if (selected.length > 0) {
                                            select.val(selected).trigger('change');
                                        }
                                    }
                                }, 200);
                            }
                        }
                    }, 200);
                });
                
                Livewire.on('company-packages:init-moi-select', (event) => {
                    setTimeout(function() {
                        const select = $('#company-moi-select');
                        if (select.length) {
                            // MOI values are strings, convert to strings array
                            const selected = (event.selected || []).map(v => String(v));
                            if (select.hasClass('select2-hidden-accessible')) {
                                // Select2 already initialized, just update values
                                select.val(selected).trigger('change');
                            } else {
                                // Select2 not initialized yet, initialize it first
                                initializeSelect2();
                                setTimeout(function() {
                                    if (select.hasClass('select2-hidden-accessible')) {
                                        if (selected.length > 0) {
                                            select.val(selected).trigger('change');
                                        }
                                    }
                                }, 200);
                            }
                        }
                    }, 200);
                });
                
                Livewire.on('company-packages:init-packages-select', (event) => {
                    setTimeout(function() {
                        const select = $('#company-packages-select');
                        if (select.length) {
                            const selected = (event.selected || []).map(v => String(v));
                            if (select.hasClass('select2-hidden-accessible')) {
                                // Select2 already initialized, just update values
                                select.val(selected).trigger('change');
                            } else {
                                // Select2 not initialized yet, initialize it first
                                initializeSelect2();
                                setTimeout(function() {
                                    if (select.hasClass('select2-hidden-accessible')) {
                                        if (selected.length > 0) {
                                            select.val(selected).trigger('change');
                                        }
                                    }
                                }, 200);
                            }
                        }
                    }, 200);
                });
                
                
                // Also listen for DOM morph updates (when modal is shown/hidden)
                Livewire.hook('morph.updated', ({ el, component }) => {
                    // Only initialize if modal is visible and Select2 is not yet initialized
                    setTimeout(function() {
                        const modal = $('#company-packages-modal');
                        if (modal.length && modal.is(':visible') && !select2Initialized) {
                            initializeSelect2();
                            select2Initialized = true;
                        }
                    }, 100);
                });
            });
        </script>
    @endpush
@endonce