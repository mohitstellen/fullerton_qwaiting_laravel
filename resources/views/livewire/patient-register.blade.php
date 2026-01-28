<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
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
    <div class="max-w-4xl w-full space-y-8">
        <!-- Logo -->
        <div class="text-center mb-6">
            <img src="{{ url($logo) }}" alt="Fullerton Health" class="mx-auto max-w-xs h-auto" style="max-height: 120px;" />
        </div>

        @if (session()->has('success'))
        <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/30 dark:text-green-200">
            {{ session('success') }}
        </div>
        @endif

        @if (session()->has('error'))
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-900/30 dark:text-red-200">
            {{ session('error') }}
        </div>
        @endif

        <!-- Sign Up Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 max-h-[80vh] overflow-y-auto">
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
                            <select wire:model.live="salutation"
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
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Mobile Number (Login ID) <span class="text-red-500">*</span>
                        </label>

                        <!-- Country code + Mobile number + Country in one row -->
                        <div class="flex gap-2 items-start">
                            <select wire:model.live="mobile_country_code"
                                class="block w-24 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                @foreach($phoneCodeCountries as $country)
                                <option value="{{ $country->phonecode }}">{{ $country->phonecode }}</option>
                                @endforeach
                            </select>

                            <input type="text"
                                wire:model.live.debounce.500ms="mobile_number"
                                placeholder="Mobile Number"
                                class="block flex-1 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">

                            @if($showCountryField)
                            <div class="flex-1">
                                <select wire:model.live="country_id"
                                    class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                    <option value="">Select Country</option>
                                    @foreach($allCountries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                                @error('country_id')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            @endif
                        </div>

                        @error('mobile_country_code')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror

                        @error('mobile_number')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>


                    <!-- Email Address -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" wire:model.live.debounce.500ms="email" placeholder="Email Address"
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
                        <input type="email" wire:model.live="confirm_email" placeholder="Confirm Email Address"
                            class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        @error('confirm_email')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror

                        @if($email_otp_sent && !$email_otp_verified)
                        <div class="mt-3 space-y-2" wire:poll.1s="updateCountdown">
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <span>Verification code sent to your email.</span>
                                @if($email_otp_countdown > 0)
                                <span class="text-blue-600 dark:text-blue-400">
                                    ({{ sprintf('%02d:%02d', floor($email_otp_countdown / 60), $email_otp_countdown % 60) }})
                                </span>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <input type="text" wire:model="email_verification_code" placeholder="Verification Code" maxlength="6"
                                    class="block w-40 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                                <button type="button" wire:click="verifyEmailCode"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Verify
                                </button>
                            </div>
                            @error('email_verification_code')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                            <button type="button" wire:click="resendEmailVerificationCode"
                                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                Resend OTP (valid for 5 mins)
                            </button>
                        </div>
                        @elseif($email_otp_verified)
                        <div class="mt-2 text-sm text-green-600 dark:text-green-400">
                            ✓ Email verified successfully
                        </div>
                        @endif
                    </div>

                    <!-- Nationality -->
                    @if($showNationalityField)
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
                    @endif

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

                        <p class="text-red-500 text-xs mt-1 block font-medium">Free text not allowed in company. Please select from the drop down list.</p>
                        @error('company_id')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 mt-1">
                            <input type="checkbox" wire:model="terms_and_conditions" id="terms_and_conditions"
                                class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 cursor-pointer"
                                style="accent-color: #2563eb;">
                        </div>
                        <label for="terms_and_conditions" class="flex-1 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            I agree to the <a href="#" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 underline">Terms and Conditions</a> <span class="text-red-500">*</span>
                        </label>
                    </div>
                    @error('terms_and_conditions')
                    <span class="text-red-500 text-xs mt-1 block ml-8">{{ $message }}</span>
                    @enderror
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

<script>
    document.addEventListener('livewire:init', function() {
        // Listen for email already exists event
        Livewire.on('swal:email-exists', () => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Email Already Exists',
                    text: 'This email address is already registered. Please use a different email address.',
                    confirmButtonText: 'OK'
                });
            }
        });

        // Listen for mobile number already exists event
        Livewire.on('swal:mobile-exists', () => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Mobile Number Already Exists',
                    text: 'This mobile number is already registered. Please use a different mobile number.',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
</script>