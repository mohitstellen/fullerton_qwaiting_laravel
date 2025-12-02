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
            <form action="{{ route('tenant.public-user.index') }}" method="GET">
                <div class="flex gap-3 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">NRIC / FIN / Passport</label>
                        <input type="text" name="searchNric" value="{{ request('searchNric') }}"
                               placeholder="NRIC / FIN / Passport"
                               class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Mobile Number</label>
                        <input type="text" name="searchMobile" value="{{ request('searchMobile') }}"
                               placeholder="Mobile Number"
                               class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Name</label>
                        <input type="text" name="searchName" value="{{ request('searchName') }}"
                               placeholder="Name"
                               class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Email</label>
                        <input type="text" name="searchEmail" value="{{ request('searchEmail') }}"
                               placeholder="Email"
                               class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Company</label>
                        <input type="text" name="searchCompany" value="{{ request('searchCompany') }}"
                               placeholder="Company"
                               class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 h-11 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 whitespace-nowrap">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Search
                        </button>
                        <a href="{{ route('tenant.public-user.index') }}"
                           class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 h-11 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900 whitespace-nowrap">
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Add User Button -->
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $editing ? 'Edit User' : 'Add User' }}
            </h2>
            <a href="{{ route('tenant.public-user.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
        </div>

        <!-- {{ $editing ? 'Edit User' : 'Add User' }} Form -->
        <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <form wire:submit.prevent="save" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Identification Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Identification Type <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="identification_type"
                            class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="">Select</option>
                            @foreach($identificationTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('identification_type')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- NRIC / FIN -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            NRIC / FIN <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="nric_fin" placeholder="NRIC / FIN"
                            class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        @error('nric_fin')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Full Name with Salutation -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <select wire:model="salutation"
                                class="block w-28 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="">Select</option>
                                @foreach($salutations as $salutationOption)
                                <option value="{{ $salutationOption }}">{{ $salutationOption }}</option>
                                @endforeach
                            </select>
                            <input type="text" wire:model="full_name" placeholder="Full Name"
                                class="block flex-1 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
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
                            Date Of Birth <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="date" wire:model="date_of_birth"
                                class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white" onclick="this.showPicker()">
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
                            class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="">Select</option>
                            @foreach($genders as $genderOption)
                            <option value="{{ $genderOption }}">{{ $genderOption }}</option>
                            @endforeach
                        </select>
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
                                class="block w-28 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                @foreach($phoneCodeCountries as $country)
                                <option value="{{ $country->phonecode }}">+{{ $country->phonecode }}</option>
                                @endforeach
                            </select>
                            <input type="text" wire:model="mobile_number" placeholder="Mobile Number"
                                class="block flex-1 rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
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
                            class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        @error('email')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Status
                        </label>
                        <select wire:model="status"
                            class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        @error('status')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Nationality -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Nationality <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="nationality"
                            class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="">Select nationality</option>
                            @foreach($nationalities as $nationalityOption)
                            <option value="{{ $nationalityOption }}">{{ $nationalityOption }}</option>
                            @endforeach
                        </select>
                        @error('nationality')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Company with autocomplete -->
                    <div x-data="companyAutocomplete()" x-init="init()">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Company <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                x-model="search"
                                x-on:input="filterCompanies()"
                                x-on:focus="showDropdown = true"
                                x-on:click.away="showDropdown = false"
                                placeholder="Search and select company"
                                class="block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base h-11 px-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">

                            <!-- Dropdown -->
                            <div x-show="showDropdown && filteredCompanies.length > 0"
                                x-transition
                                class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto">
                                <template x-for="company in filteredCompanies" :key="company.id">
                                    <div x-on:click="selectCompany(company)"
                                        class="px-4 py-2.5 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 text-base text-gray-900 dark:text-gray-100">
                                        <span x-text="company.name"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        @error('company_id')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @if($message == 'The company id field is required.')
                        <span class="text-red-500 text-xs mt-1 block">Mandatory for Corporate Clients. Free text not allowed in company. Please select from the drop down list.</span>
                        @endif
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('tenant.public-user.index') }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ $editing ? 'Update' : 'Save' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Alpine.js component for company autocomplete
    function companyAutocomplete() {
        return {
            search: '',
            showDropdown: false,
            companies: @json($companies->map(fn($c) => ['id' => $c->id, 'name' => $c->company_name])),
            filteredCompanies: [],

            init() {
                const companyId = @this.company_id;
                if (companyId) {
                    const company = this.companies.find(c => c.id == companyId);
                    if (company) {
                        this.search = company.name;
                    }
                }
            },

            filterCompanies() {
                if (!this.search || this.search.length < 1) {
                    this.filteredCompanies = [];
                    return;
                }
                this.filteredCompanies = this.companies.filter(c =>
                    c.name.toLowerCase().includes(this.search.toLowerCase())
                ).slice(0, 10);
            },

            selectCompany(company) {
                this.search = company.name;
                @this.set('company_id', company.id);
                @this.set('company_search', company.name);
                this.showDropdown = false;
            }
        }
    }
</script>
@endpush