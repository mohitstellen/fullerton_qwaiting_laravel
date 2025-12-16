<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        Profile
    </h1>

    @if (session()->has('profile_success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('profile_success') }}</span>
        </div>
    @endif

    @if (session()->has('profile_error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('profile_error') }}</span>
        </div>
    @endif

    <form wire:submit.prevent="updateProfile" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Identification Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Identification Type <span class="text-red-500">*</span>
                </label>
                <select wire:model.defer="identification_type" 
                    disabled
                    class="w-full p-2 px-3 border rounded-md border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                    @foreach($identificationTypes as $type)
                        <option value="{{ $type }}" {{ $identification_type === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
                @error('identification_type') 
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <!-- NRIC / FIN or Passport -->
            <div>
                @if($identification_type === 'NRIC / FIN')
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        NRIC / FIN <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                        value="{{ $nric_fin ?? '' }}" 
                        disabled
                        class="w-full p-2 px-3 border rounded-md border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                        placeholder="Enter NRIC / FIN">
                    @error('nric_fin') 
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                    @enderror
                @else
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Passport No <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                        value="{{ $passport ?? '' }}" 
                        disabled
                        class="w-full p-2 px-3 border rounded-md border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                        placeholder="Enter Passport Number">
                    @error('passport') 
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                    @enderror
                @endif
            </div>

            <!-- Full Name (Title + Name) -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <select wire:model.defer="salutation" 
                        class="w-24 p-2 px-3 border rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                        @foreach($salutations as $sal)
                            <option value="{{ $sal }}">{{ $sal }}</option>
                        @endforeach
                    </select>
                    <input type="text" wire:model.defer="full_name" 
                        class="flex-1 p-2 px-3 border rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter full name">
                </div>
                @error('full_name') 
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <!-- Date of Birth -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Date of Birth <span class="text-red-500">*</span>
                </label>
                <input type="date" wire:model.defer="date_of_birth" 
                    class="w-full p-2 px-3 border rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500" onclick="this.showPicker()">
                @error('date_of_birth') 
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <!-- Gender -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Gender <span class="text-red-500">*</span>
                </label>
                <select wire:model.defer="gender" 
                    class="w-full p-2 px-3 border rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    @foreach($genders as $gen)
                        <option value="{{ $gen }}">{{ $gen }}</option>
                    @endforeach
                </select>
                @error('gender') 
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <!-- Mobile Number (Login ID) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mobile Number (Login ID) <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <select wire:model.defer="mobile_country_code" 
                        disabled
                        class="w-40 p-2 px-3 border rounded-md border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                        @foreach($countryCodes as $code => $label)
                            <option value="{{ $code }}" {{ $mobile_country_code === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="text" 
                        value="{{ $mobile_number }}" 
                        disabled
                        class="flex-1 p-2 px-3 border rounded-md border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                        placeholder="Enter mobile number">
                </div>
                @error('mobile_number') 
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <!-- Email Address -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input type="email" wire:model.defer="email" 
                    class="w-full p-2 px-3 border rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter email address">
                @error('email') 
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <!-- Nationality -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nationality <span class="text-red-500">*</span>
                </label>
                <select wire:model.defer="nationality" 
                    class="w-full p-2 px-3 border rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Nationality</option>
                    @foreach($nationalities as $nat)
                        <option value="{{ $nat }}" {{ $nationality === $nat ? 'selected' : '' }}>{{ $nat }}</option>
                    @endforeach
                </select>
                @error('nationality') 
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <!-- Company (only for Corporate customers) -->
            @if($isCorporateCustomer)
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Company <span class="text-red-500">*</span>
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
                            wire:model.live.debounce.300ms="company_search"
                            @focus="showDropdown = true; isFocused = true"
                            @keydown.escape="showDropdown = false"
                            placeholder="Search and select company"
                            class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400" />
                        @if($company_id)
                        <button type="button" wire:click="clearCompany" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        @endif
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
                    @elseif($showCompanyDropdown && strlen($company_search) >= 1 && count($allCompanies) == 0)
                    <div class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg">
                        <div class="px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400">
                            No companies found
                        </div>
                    </div>
                    @endif
                </div>
                @if($company_search && !$company_id)
                <p class="text-red-500 text-xs mt-1">Free text not allowed in company. Please select from the drop down list.</p>
                @endif
                @error('company_id') 
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                @enderror
            </div>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="submit" 
                wire:loading.attr="disabled"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition disabled:opacity-50">
                <span wire:loading.remove wire:target="updateProfile">Update</span>
                <span wire:loading wire:target="updateProfile">Updating...</span>
            </button>
        </div>
    </form>
</div>

