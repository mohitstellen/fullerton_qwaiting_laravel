<div class="p-4">
    <div class="space-y-6">
        <!-- Search Form (same as list view) -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" placeholder="NRIC / FIN / Passport"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <input type="text" placeholder="Mobile Number"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <input type="text" placeholder="Name"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <input type="text" placeholder="Email"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <input type="text" placeholder="Company"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                </div>
                <div class="flex gap-2">
                    <button type="button"
                        class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                        Search
                    </button>
                    <button type="button"
                        class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('tenant.public-user.index') }}"
                    class="{{ request()->routeIs('tenant.public-user.*') && !request()->routeIs('tenant.public-user.index') ? 'border-yellow-500 text-yellow-600 dark:text-yellow-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    {{ $editing ? 'Edit User' : 'Add User' }}
                </a>
                <a href="{{ route('tenant.public-user.index') }}"
                    class="{{ request()->routeIs('tenant.public-user.index') ? 'border-yellow-500 text-yellow-600 dark:text-yellow-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    User List
                </a>
            </nav>
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

        <!-- {{ $editing ? 'Edit User' : 'Add User' }} Form -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <form wire:submit.prevent="save" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Identification Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            Identification Type <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="identification_type"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="">Select</option>
                            @foreach($identificationTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('identification_type')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- NRIC / FIN -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            NRIC / FIN <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="nric_fin" placeholder="NRIC / FIN"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('nric_fin')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Full Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="full_name" placeholder="Full Name"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('full_name')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Date Of Birth -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            Date Of Birth <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="date" wire:model="date_of_birth"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" onclick="this.showPicker()">
                        </div>
                        @error('date_of_birth')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            Gender <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="gender"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="">Select</option>
                            @foreach($genders as $genderOption)
                            <option value="{{ $genderOption }}">{{ $genderOption }}</option>
                            @endforeach
                        </select>
                        @error('gender')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Mobile Number (Login ID) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            Mobile Number (Login ID) <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <select wire:model="mobile_country_code"
                                class="w-1/4 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                @foreach($phoneCodeCountries as $country)
                                <option value="{{ $country->phonecode }}">+{{ $country->phonecode }}</option>
                                @endforeach
                            </select>
                            <input type="text" wire:model="mobile_number" placeholder="Mobile Number"
                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>
                        @error('mobile_number')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" wire:model="email" placeholder="Email Address"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('email')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            Status
                        </label>
                        <select wire:model="status"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        @error('status')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Nationality -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                            Nationality <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="nationality"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="">Select nationality</option>
                            @foreach($nationalities as $nationalityOption)
                            <option value="{{ $nationalityOption }}">{{ $nationalityOption }}</option>
                            @endforeach
                        </select>
                        @error('nationality')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Company with autocomplete -->
                    <div x-data="companyAutocomplete()" x-init="init()">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
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
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">

                            <!-- Dropdown -->
                            <div x-show="showDropdown && filteredCompanies.length > 0"
                                x-transition
                                class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-auto">
                                <template x-for="company in filteredCompanies" :key="company.id">
                                    <div x-on:click="selectCompany(company)"
                                        class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 text-sm text-gray-900 dark:text-gray-100">
                                        <span x-text="company.name"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        @error('company_id')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @if($message == 'The company id field is required.')
                        <span class="text-red-500 text-xs">Mandatory for Corporate Clients. Free text not allowed in company. Please select from the drop down list.</span>
                        @endif
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('tenant.public-user.index') }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                        Close
                    </a>
                    <button type="submit"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
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