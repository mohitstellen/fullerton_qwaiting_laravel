<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Dependents</h1>

    <!-- Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <nav class="-mb-px flex space-x-8">
            <button wire:click="switchTab('new')" 
                class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'new' ? 'border-orange-500 text-orange-600 dark:text-orange-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                New Dependent
            </button>
            <button wire:click="switchTab('list')" 
                class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'list' ? 'border-orange-500 text-orange-600 dark:text-orange-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                Dependent List
            </button>
        </nav>
    </div>

    <!-- Success/Error Messages -->
    @if($successMessage)
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ $successMessage }}
        </div>
    @endif

    @if($errorMessage)
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ $errorMessage }}
        </div>
    @endif

    <!-- New Dependent Form -->
    @if($activeTab === 'new')
        <form wire:submit.prevent="saveDependent" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Identification Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Identification Type <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="identificationType" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Identification Type</option>
                        <option value="NRIC">NRIC / FIN</option>
                        <option value="Passport">Passport</option>
                    </select>
                    @error('identificationType')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NRIC / FIN or Passport -->
                <div>
                    @if($identificationType === 'Passport')
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Passport <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="passport" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="Enter passport number">
                        @error('passport')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    @else
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            NRIC / FIN <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="nricFin" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="Enter NRIC / FIN">
                        @error('nricFin')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                <!-- Full Name (Salutation + Name) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <select wire:model="salutation" 
                            class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select</option>
                            @foreach($salutationOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                        <input type="text" wire:model="fullName" 
                            class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="Enter full name">
                    </div>
                    @error('salutation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('fullName')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date of Birth -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Date of Birth <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="dateOfBirth" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        placeholder="DD/MM/YYYY" 
                        id="dateOfBirth"
                        wire:ignore>
                    @error('dateOfBirth')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Gender -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Gender <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="gender" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                    @error('gender')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Relationship -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Relationship <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="relationship" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Relationship</option>
                        @foreach($relationshipOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                    @error('relationship')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-4 pt-4">
                <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    {{ $dependentId ? 'Update' : 'Submit' }}
                </button>
                <button type="button" wire:click="clearForm" 
                    class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Clear
                </button>
            </div>
        </form>
    @endif

    <!-- Dependent List -->
    @if($activeTab === 'list')
        <div class="overflow-x-auto">
            @if(count($dependents) > 0)
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Identification</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date of Birth</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Gender</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Relationship</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($dependents as $dependent)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $dependent['salutation'] ? $dependent['salutation'] . ' ' : '' }}{{ $dependent['full_name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    @if($dependent['identification_type'] === 'Passport')
                                        {{ $dependent['passport'] }}
                                    @else
                                        {{ $dependent['nric_fin'] }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $dependent['date_of_birth'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $dependent['gender'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $dependent['relationship'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="editDependent({{ $dependent['id'] }})" 
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-4">
                                        Edit
                                    </button>
                                    <button wire:click="deleteDependent({{ $dependent['id'] }})" 
                                        wire:confirm="Are you sure you want to delete this dependent?"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">No dependents found. Add a new dependent using the "New Dependent" tab.</p>
                </div>
            @endif
        </div>
    @endif
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/cdn/flatpickr.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/cdn/flatpickr.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initDatePicker();
    });
    
    // Reinitialize when Livewire updates the DOM
    document.addEventListener('livewire:load', function() {
        initDatePicker();
    });
    
    Livewire.hook('morph.updated', () => {
        setTimeout(initDatePicker, 100);
    });
    
    function initDatePicker() {
        const dateInput = document.getElementById('dateOfBirth');
        if (dateInput && !dateInput._flatpickr) {
            flatpickr("#dateOfBirth", {
                dateFormat: "d/m/Y",
                maxDate: "today",
                allowInput: true,
                onChange: function(selectedDates, dateStr, instance) {
                    @this.set('dateOfBirth', dateStr);
                }
            });
        }
    }
</script>
@endpush

