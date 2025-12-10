<div class="p-6">
    <style>
        /* Ensure checkboxes are visible */
        input[type="checkbox"] {
            appearance: auto;
            -webkit-appearance: checkbox;
            -moz-appearance: checkbox;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 0.25rem;
            background-color: white;
            cursor: pointer;
        }
        input[type="checkbox"]:checked {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        input[type="checkbox"]:focus {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
        }
    </style>

    @if (session()->has('success'))
        <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/30 dark:text-green-200 mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-900/30 dark:text-red-200 mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Logo Section -->
    <div class="max-w-4xl mx-auto mb-6 text-center">
        <img src="{{ url($logo) }}" alt="Logo" class="mx-auto max-w-xs h-auto" style="max-height: 120px;" />
    </div>

    <!-- Sign Up Form -->
    <div class="max-w-4xl mx-auto bg-white dark:bg-gray-900 rounded-lg shadow-lg">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-blue-600 dark:bg-blue-800 rounded-t-lg">
            <h2 class="text-xl font-semibold text-white">Sign Up</h2>
            <button wire:click="close" class="text-white hover:text-gray-200 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Form Content -->
        <div class="p-6 max-h-[80vh] overflow-y-auto">
            <form wire:submit.prevent="register" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Identification Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Identification Type <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="identification_type"
                            class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            @foreach($identificationTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('identification_type')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- NRIC / FIN or Passport -->
                    <div>
                        @if($identification_type === 'NRIC / FIN')
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                NRIC / FIN <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="nric_fin" placeholder="NRIC / FIN"
                                class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                            @error('nric_fin')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        @elseif($identification_type === 'Passport')
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Passport <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="passport" placeholder="Passport"
                                class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                            @error('passport')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        @endif
                    </div>

                    <!-- Full Name with Salutation -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <select wire:model="salutation"
                                class="block w-28 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                @foreach($salutations as $salutationOption)
                                <option value="{{ $salutationOption }}">{{ $salutationOption }}</option>
                                @endforeach
                            </select>
                            <input type="text" wire:model="full_name" placeholder="Full Name"
                                class="block flex-1 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        </div>
                        @error('salutation')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                        @error('full_name')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Date Of Birth -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Date of Birth <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="date" wire:model="date_of_birth"
                                class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="DD/MM/YYYY" onclick="this.showPicker()">
                        </div>
                        @error('date_of_birth')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Gender <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="gender"
                            class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            @foreach($genders as $genderOption)
                            <option value="{{ $genderOption }}">{{ $genderOption }}</option>
                            @endforeach
                        </select>
                        <p class="text-red-500 text-xs mt-1">Please input the correct gender as gender cannot be changed.</p>
                        @error('gender')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Mobile Number (Login ID) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Mobile Number (Login ID) <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <select wire:model="mobile_country_code"
                                class="block w-24 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                @foreach($phoneCodeCountries as $country)
                                <option value="{{ $country->phonecode }}">{{ $country->phonecode }}</option>
                                @endforeach
                            </select>
                            <input type="text" wire:model="mobile_number" placeholder="Mobile Number"
                                class="block flex-1 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        </div>
                        @error('mobile_number')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" wire:model="email" placeholder="Email Address"
                            class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        @error('email')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Confirm Email Address -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Confirm Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" wire:model="confirm_email" placeholder="Confirm Email Address"
                            class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        @error('confirm_email')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Nationality -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Nationality <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="nationality"
                            class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            @if($nationalities)
                                @foreach($nationalities as $nationalityOption)
                                <option value="{{ $nationalityOption }}">{{ $nationalityOption }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('nationality')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Company -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Company
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
                                    placeholder="Mandatory for Corporate Clients"
                                    class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400" />
                                @if($company_id)
                                <button type="button" wire:click="clearCompany" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
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
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Consent Checkboxes -->
                <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <!-- First Consent -->
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 mt-1">
                            <input type="checkbox" wire:model="consent_data_collection" id="consent_data_collection"
                                class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 cursor-pointer"
                                style="accent-color: #2563eb;">
                        </div>
                        <label for="consent_data_collection" class="flex-1 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            I confirm the information provided above is accurate and hereby consent to Fullerton Health Group collecting, using and disclosing my personal data for the purpose of providing me with the services I have requested for and other related purposes. Please refer to Fullerton Health Group's Privacy Policy at (www.fullertonhealth.com/privacy-policy). We would like to keep you updated with our latest news, offers, and promotions. To do this, we need your consent to send you marketing communications.
                        </label>
                    </div>
                    @error('consent_data_collection')
                    <span class="text-red-500 text-xs mt-1 block ml-8">{{ $message }}</span>
                    @enderror

                    <!-- Second Consent -->
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 mt-1">
                            <input type="checkbox" wire:model="consent_marketing" id="consent_marketing"
                                class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 cursor-pointer"
                                style="accent-color: #2563eb;">
                        </div>
                        <label for="consent_marketing" class="flex-1 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            By ticking the box, you agree to receive marketing communications from Fullerton Healthcare Group and its affiliates. You can withdraw your consent at any time by clicking the unsubscribe link in any of our emails or by submitting your request in writing to comms@fullertonhealth.com.
                        </label>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" wire:click="close"
                        class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        Close
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Sign Up
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
