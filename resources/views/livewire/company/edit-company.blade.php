<div class="p-4">
    <div class="space-y-6 max-w-3xl mx-auto">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Edit Company</h2>
            <a href="{{ route('tenant.companies.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                Back to list
            </a>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <form wire:submit.prevent="update" class="space-y-6">
                @include('livewire.company.company-form')

                <div class="flex items-center justify-end gap-3">
                    <button type="submit"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                        Update
                    </button>
                </div>
            </form>
        </div>

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

                <div class="space-y-3">
                    @forelse ($companyPackages as $mapping)
                        @php
                            $clinicNames = collect($mapping['clinic_ids'])
                                ->map(fn ($id) => $locationLookup[$id] ?? null)
                                ->filter()
                                ->values()
                                ->all();
                        @endphp
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900" wire:key="company-package-{{ $mapping['id'] }}">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Appointment type</p>
                                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $mapping['appointment_type_name'] }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button"
                                            wire:click="editMapping({{ $mapping['id'] }})"
                                            class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                                        Edit
                                    </button>
                                    <button type="button"
                                            wire:click="deleteMapping({{ $mapping['id'] }})"
                                            class="inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900/40 dark:text-red-300 dark:hover:bg-red-900/20">
                                        Delete
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-4 text-sm text-gray-700 dark:text-gray-200 md:grid-cols-2">
                                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Package</p>
                                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">{{ $mapping['package_name'] }}</p>
                                </div>
                                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Clinics</p>
                                    @if (count($clinicNames))
                                        <p class="mt-1 leading-relaxed text-gray-800 dark:text-gray-100">{{ implode(', ', $clinicNames) }}</p>
                                    @else
                                        <p class="mt-1 text-gray-400">Not specified</p>
                                    @endif
                                </div>

                                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/40 md:col-span-2">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Modes of identification</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @forelse ($mapping['modes_of_identification'] as $moi)
                                            <span class="inline-flex items-center rounded-full bg-white px-3 py-0.5 text-xs font-medium text-gray-700 shadow-sm ring-1 ring-gray-200 dark:bg-gray-900 dark:text-gray-100 dark:ring-gray-700">{{ $moi }}</span>
                                        @empty
                                            <span class="text-gray-400">Not specified</span>
                                        @endforelse
                                    </div>
                                </div>
                                @if (! empty($mapping['remarks']))
                                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/40 md:col-span-2">
                                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Remarks</p>
                                        <p class="mt-1 text-gray-800 dark:text-gray-100">{{ $mapping['remarks'] }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                Updated {{ $mapping['updated_at'] ?? '—' }}
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-300">
                            No company packages have been added yet.
                        </div>
                    @endforelse
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
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Appointment type *</label>
                                            <select wire:model.live="mappingForm.appointment_type_id"
                                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                <option value="">Select appointment type</option>
                                                @foreach ($appointmentTypes as $type)
                                                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                                @endforeach
                                            </select>
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
                                                <div class="mt-1 max-h-64 overflow-y-auto rounded-lg border border-gray-300 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                                    {{-- Select All checkbox --}}
                                                    <label class="mb-3 inline-flex items-center space-x-2 border-b border-gray-200 pb-2 text-sm font-semibold text-gray-800 dark:border-gray-700 dark:text-gray-300">
                                                        <input type="checkbox"
                                                            wire:click="toggleSelectAllPackages"
                                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600"
                                                            @if($this->areAllPackagesSelected()) checked @endif>
                                                        <span>Select All</span>
                                                    </label>
                                                    
                                                    {{-- Individual package checkboxes --}}
                                                    <div class="mt-2 space-y-2">
                                                        @foreach ($packageOptions as $package)
                                                            <label class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-200">
                                                                <input type="checkbox"
                                                                    wire:model="mappingForm.package_ids"
                                                                    value="{{ $package['id'] }}"
                                                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                                                                <span>{{ $package['name'] }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
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

@once
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
                        
                        // Check if any Select2 needs initialization
                        if ((clinicsSelect.length && !clinicsSelect.hasClass('select2-hidden-accessible')) ||
                            (moiSelect.length && !moiSelect.hasClass('select2-hidden-accessible'))) {
                            initializeSelect2();
                            select2Initialized = true;
                        }
                    } else if (!modal.length || !modal.is(':visible')) {
                        // Modal is closed - destroy Select2 instances for clean reinitialization
                        if (select2Initialized) {
                            destroySelect2('#company-clinics-select');
                            destroySelect2('#company-moi-select');
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
                    
                    // Prevent modal from closing when clicking on Select2 elements
                    $(document).off('click.select2-modal', '.select2-container, .select2-dropdown');
                    $(document).on('click.select2-modal', '.select2-container, .select2-dropdown', function(e) {
                        e.stopPropagation();
                    });
                    
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