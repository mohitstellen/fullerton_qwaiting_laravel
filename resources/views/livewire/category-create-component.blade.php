@php
use Illuminate\Support\Facades\Storage;
@endphp

<div class="p-4">

    <h2 class="text-xl font-semibold dark:text-white/90 mb-4">
        @if($tab == 2)
        {{ $isEdit ? __('text.Edit Package') : __('text.Add Package') }}
        @else
        {{ $isEdit ? __('text.Edit Appointment Type') : __('text.Add Appointment Type') }}
        @endif
    </h2>

    <div class="p-4 md:p-5 rounded-lg shadow border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-gray-100 dark:border-gray-800">
            <div class="p-1">

                <form wire:submit.prevent="saveCategory">
                    <div class="space-y-5">
                        <div class="-mx-2.5 flex flex-wrap gap-y-5">
                            @if($tab > 1 && !empty($parentCategory))
                            <div class="w-full px-2.5 xl:w-1/2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{ $tab == 2 ? __('text.appointment type') : __('text.Parent Appointment Type') }}<span class="text-red-500">*</span>
                                </label>
                                <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                                    <select
                                        wire:model="parent_id"
                                        class="dark:bg-dark-900 z-20 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                        :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                        @change="isOptionSelected = true">
                                        <option value="">{{__('text.select')}}</option>
                                        @foreach($parentCategory as $category)
                                        <option value="{{ $category->id }}" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                            {{ $category->name ?? 'None'}}
                                        </option>
                                        @endforeach


                                    </select>
                                    <span class="absolute z-30 text-gray-500 -translate-y-1/2 right-4 top-1/2 dark:text-gray-400 rtl:right-auto rtl:left-3">
                                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>
                                @error('parent_id') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            @endif


                            <div class="w-full px-2.5 xl:w-1/2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{__('text.name')}}<span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="name"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                @error('name') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="w-full px-2.5 xl:w-1/2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{ $tab == 2 ? __('text.Package Name (Other Language)') : __('text.Appointment Type Name (Other Language)') }}
                                </label>
                                <input
                                    type="text"
                                    wire:model="other_name"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                @error('other_name') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="w-full px-2.5 xl:w-1/2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{__('text.Acronym')}}
                                </label>
                                <input
                                    type="text"
                                    wire:model="acronym"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                @error('acronym') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            @if($tab == 2)
                            <div class="w-full px-2.5 xl:w-1/2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{__('text.Package for')}}<span class="text-red-500">*</span>
                                </label>
                                <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                                    <select
                                        wire:model="package_for"
                                        class="dark:bg-dark-900 z-20 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                        :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                        @change="isOptionSelected = true">
                                        <option value="">{{__('text.select')}}</option>
                                        <option value="Both" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">Both</option>
                                        <option value="Self" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">Self</option>
                                        <option value="Dependent" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">Dependent</option>
                                    </select>
                                    <span class="absolute z-30 text-gray-500 -translate-y-1/2 right-4 top-1/2 dark:text-gray-400 rtl:right-auto rtl:left-3">
                                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>
                                @error('package_for') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            @endif

                            <div class="w-full px-2.5 xl:w-1/2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{__('text.Sort')}}
                                </label>
                                <input
                                    type="number"
                                    wire:model="sort"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                @error('sort') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>


                            <div class="w-full px-2.5 xl:w-1/2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{__('text.Available for Public Booking')}}
                                </label>
                                <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                                    <select
                                        wire:model="display_on"
                                        class="dark:bg-dark-900 z-20 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                        :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                        @change="isOptionSelected = true">
                                        <option value="Display on Transfer & Ticket Screen" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                            {{ __('text.Display on Transfer & Ticket Screen')}}
                                        </option>
                                        <option value="Ticket Screen" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                            {{ __('text.Ticket Screen')}}
                                        </option>

                                        <option value="Transfer Screen" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                            {{ __('text.Transfer Screen')}}
                                        </option>
                                    </select>
                                    <span class="absolute z-30 text-gray-500 -translate-y-1/4 right-4 top-1/2 dark:text-gray-400 rtl:right-auto rtl:left-3">
                                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>
                                @error('display_on') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="w-full px-2.5 xl:w-1/2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{__('text.For Screen')}}
                                </label>
                                <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                                    <select
                                        wire:model="booking_category_show_for"
                                        class="dark:bg-dark-900 z-20 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                        :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                        @change="isOptionSelected = true">
                                        <option value="Backend & Online Appointment Screen" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                            {{ __('text.Display on Backend & Online Appointment Screen')}}
                                        </option>
                                        <option value="Backend" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                            {{ __('text.Backend')}}
                                        </option>

                                        <option value="Online" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                            {{ __('text.Online Appointment Screen')}}
                                        </option>
                                    </select>
                                    <span class="absolute z-30 text-gray-500 -translate-y-1/4 right-4 top-1/2 dark:text-gray-400 rtl:right-auto rtl:left-3">
                                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>
                                @error('booking_category_show_for') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="w-full px-2.5 xl:w-1/2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{ __('text.visitor in queue')}}
                                </label>
                                <input
                                    type="number"
                                    wire:model="visitor_in_queue"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                @error('visitor_in_queue') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            @if($tab == 1)
                            <div class="w-full px-2.5 xl:w-1/2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{ __('text.Lead Time') }}
                                </label>
                                <div class="flex flex-col gap-3 sm:flex-row">
                                    <input
                                        type="number"
                                        min="0"
                                        wire:model="leadTimeValue"
                                        class="dark:bg-dark-900 h-11 flex-1 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                    <div class="relative sm:w-40">
                                        <select
                                            wire:model="leadTimeUnit"
                                            class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                            <option value="minutes">{{ __('text.Minutes') }}</option>
                                            <option value="hours">{{ __('text.Hours') }}</option>
                                            <option value="days">{{ __('text.Days') }}</option>
                                        </select>
                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 dark:text-gray-400">
                                            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-1">
                                    @error('leadTimeValue') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                    @error('leadTimeUnit') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @endif
                            @if($this->paymentSet && $this->paymentSet->enable_payment == 1 && $this->paymentSet->category_level == $tab)

                            <div class="w-full px-2.5 xl:w-1/2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{ $tab == 2 ? __('text.Select package is paid or free') : __('text.Select appointment type is paid or free') }}
                                </label>
                                <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                                    <select
                                        wire:model.live="is_paid"
                                        class="dark:bg-dark-900 z-20 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                        :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                        @change="isOptionSelected = true">
                                        <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                            Select
                                        </option>
                                        <option value="0" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                            Free
                                        </option>
                                        <option value="1" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                            Paid
                                        </option>
                                    </select>
                                    <span class="absolute z-30 text-gray-500 -translate-y-1/2 right-4 top-1/2 dark:text-gray-400 rtl:right-auto rtl:left-3">
                                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>

                                @error('is_paid') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            @if($is_paid == 1)
                            <div class="w-full px-2.5 xl:w-1/2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{ $tab == 2 ? __('text.Package Amount') : __('text.Appointment Type Amount') }}
                                </label>
                                <input
                                    type="text"
                                    wire:model="amount"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                @error('amount') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            @endif
                            @endif
                            {{-- <div class="w-full px-2.5 xl:w-1/2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Image</label>
            <input type="file" wire:model="img" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
            @if ($img)
                <img src="{{ $img->temporaryUrl() }}" class="mt-2 h-24">
                            @elseif ($isEdit && $category->img)
                            <img src="{{ url('storage/' . $category->img) }}" class="mt-2 h-24">
                            @endif
                        </div> --}}

                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ __('text.Image')}}
                            </label>
                            <input type="file" wire:model="img" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('img')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            @if ($img)
                            @if (Str::startsWith($img->getMimeType(), 'image/'))
                            <div class="mt-2 relative w-fit">
                                <img src="{{ $img->temporaryUrl() }}" class="h-24">
                                <button type="button" wire:click="removeTempImage"
                                    class="absolute top-0 right-0 bg-red-500 text-white text-xs px-1 py-0.5 rounded-full">✕</button>
                            </div>
                            @else
                            <div class="mt-2">
                                <p class="text-sm text-gray-600">Uploaded file: {{ $img->getClientOriginalName() }}</p>
                                <button type="button" wire:click="removeTempImage"
                                    class="mt-1 bg-red-500 text-white text-xs px-2 py-1 rounded">Remove File</button>
                            </div>
                            @endif
                            @elseif ($isEdit && $category->img)
                            <div class="mt-2 relative w-fit">
                                <img src="{{ url('storage/' . $category->img) }}" class="h-24">
                                <button type="button" wire:click="deleteOldImage"
                                    class="absolute top-0 right-0 bg-red-500 text-white text-xs px-1 py-0.5 rounded-full">✕</button>
                            </div>
                            @endif
                        </div>

                        <div class="w-full px-2.5 xl:w-1/2">
                            <div class="mb-1.5">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('text.Locations') }}*</label>
                                <div wire:ignore>
                                    <select wire:model="locations" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" multiple id="locations-select">
                                        @if(!empty($allLocations))
                                        @foreach($allLocations as $location)
                                        <option value="{{ $location->id }}">{{ $location->location_name }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                                @error('locations') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        @if($isEdit)
                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{__('text.Company')}}
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
                                        wire:model.live.debounce.300ms="companySearch"
                                        @focus="showDropdown = true; isFocused = true"
                                        @keydown.escape="showDropdown = false"
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
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
                                @elseif($showCompanyDropdown && strlen($companySearch) >= 1 && count($allCompanies) == 0)
                                <div class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg">
                                    <div class="px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400">
                                        {{__('text.No companies found')}}
                                    </div>
                                </div>
                                @endif
                            </div>
                            @error('company_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{__('text.Redirect Url')}}
                            </label>
                            <input
                                type="url"
                                wire:model="redirectUrl"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />


                            @error('redirectUrl') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="w-full px-2.5">
                            <label class="switch">
                                <input type="checkbox" {{ $isService == true ? 'checked' :'' }}
                                    wire:model.live="isService">
                                <span class="slider round"></span>
                            </label>
                            {{ $tab == 2 ? __('text.Enable/Disable Package Note.') : __('text.Enable/Disable Appointment Type Note.') }}
                        </div>

                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ $tab == 2 ? __('text.Package Time (in mins)') : __('text.Appointment Type Time (in mins)') }}
                            </label>
                            <input
                                type="number"
                                wire:model="serviceTime"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('serviceTime') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ $tab == 2 ? __('text.Package Note') : __('text.Appointment Type Note') }}
                            </label>
                            <textarea wire:model="note" row="5" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>

                            @error('note') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{__('text.description')}}
                            </label>
                            @if($tab == 2)
                                {{-- Rich text editor for packages --}}
                                <div wire:ignore>
                                    <div
                                        id="description-editor"
                                        class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                        style="height: 200px; overflow-y: auto;"></div>
                                </div>
                            @else
                                {{-- Plain textarea for appointment types --}}
                                <textarea wire:model="description" row="5" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                            @endif
                            @error('description') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{__('text.Ticket Note')}}
                            </label>
                            <textarea wire:model="ticket_note" row="5" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>

                            @error('ticket_note') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ __('text.Label Image')}}
                            </label>
                            <input type="file" wire:model="label_image" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('label_image')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            @if ($label_image)
                            @if (Str::startsWith($label_image->getMimeType(), 'image/'))
                            <div class="mt-2 relative w-fit">
                                <img src="{{ $label_image->temporaryUrl() }}" class="h-24">
                                <button type="button" wire:click="removeTempImage"
                                    class="absolute top-0 right-0 bg-red-500 text-white text-xs px-1 py-0.5 rounded-full">✕</button>
                            </div>
                            @else
                            <div class="mt-2">
                                <p class="text-sm text-gray-600">Uploaded file: {{ $label_image->getClientOriginalName() }}</p>
                                <button type="button" wire:click="removeTempImage"
                                    class="mt-1 bg-red-500 text-white text-xs px-2 py-1 rounded">Remove File</button>
                            </div>
                            @endif
                            @elseif ($isEdit && $category->label_image)
                            <div class="mt-2 relative w-fit">
                                <img src="{{ url('storage/' . $category->label_image) }}" class="h-24">
                                <button type="button" wire:click="deleteOldLabelImage"
                                    class="absolute top-0 right-0 bg-red-500 text-white text-xs px-1 py-0.5 rounded-full">✕</button>
                            </div>
                            @endif
                        </div>

                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ __('text.Color') }}
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="color" wire:model="service_color"
                                    class="h-9 w-16 rounded border border-gray-300 bg-transparent dark:border-gray-700 dark:bg-gray-900" />
                                <input type="text" wire:model="service_color"
                                    class="dark:bg-dark-900 h-9 flex-1 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                    placeholder="#000000" />
                            </div>
                            @error('service_color')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ __('text.Label Background Color') }}
                            </label>
                            <div class="mt-1 flex items-center gap-3">
                                <input
                                    type="color"
                                    wire:model="label_background_color"
                                    class="h-9 w-16 rounded border border-gray-300 bg-transparent dark:border-gray-700 dark:bg-gray-900" />
                                <input
                                    type="text"
                                    wire:model="label_background_color"
                                    class="dark:bg-dark-900 h-9 flex-1 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                    placeholder="#000000" />
                            </div>
                        </div>

                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ __('text.Label Font Color') }}
                            </label>
                            <div class="mt-1 flex items-center gap-3">
                                <input
                                    type="color"
                                    wire:model="label_font_color"
                                    class="h-9 w-16 rounded border border-gray-300 bg-transparent dark:border-gray-700 dark:bg-gray-900" />
                                <input
                                    type="text"
                                    wire:model="label_font_color"
                                    class="dark:bg-dark-900 h-9 flex-1 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                    placeholder="#FFFFFF" />
                            </div>
                        </div>

                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ __('text.Label Text') }}
                            </label>
                            <input
                                type="text"
                                wire:model="label_text"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            @error('label_text')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ __('text.Background Color') }}
                            </label>
                            <div class="mt-1 flex items-center gap-3">
                                <input
                                    type="color"
                                    wire:model="bg_color"
                                    class="h-9 w-16 rounded border border-gray-300 bg-transparent dark:border-gray-700 dark:bg-gray-900" />
                                <input
                                    type="text"
                                    wire:model="bg_color"
                                    class="dark:bg-dark-900 h-9 flex-1 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                    placeholder="#4F46E5" />
                            </div>
                        </div>

                        @if($tab == 2)
                        <div class="w-full px-2.5 xl:w-1/2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ __('text.Enable eVoucher') }}
                            </label>
                            <div class="relative">
                                <select
                                    wire:model="enableEVoucher"
                                    class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                    <option value="0">{{ __('text.no') }}</option>
                                    <option value="1">{{ __('text.yes') }}</option>
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 dark:text-gray-400">
                                    <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                            @error('enableEVoucher') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        {{-- Email Templates Section --}}
                        @if($tab == '1')
                        <div class="w-full px-2.5">
                            <h3 class="text-lg font-semibold dark:text-white/90 mb-4 mt-6">{{ __('text.Email Templates') }}</h3>

                            {{-- Email Templates in Column Layout --}}
                            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
                            {{-- Confirmation Communication --}}
                            <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 shadow-sm">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{ __('text.Confirmation Communication') }} <span class="text-red-500">*</span>
                                </label>
                                
                                {{-- Variable Selector for Confirmation --}}
                                <div class="mb-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <label class="mb-2 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                        {{ __('text.Select Variable') }}
                                    </label>
                                    <div class="relative">
                                        <select 
                                            wire:model="selectedVariableConfirmation"
                                            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                            <option value="">{{ __('text.Select Variable') }}</option>
                                            @foreach($variables as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mt-2 flex gap-2">
                                        <button 
                                            type="button"
                                            wire:click="appendToConfirmationSubject"
                                            onclick="this.blur()"
                                            class="flex-1 px-2 py-1.5 text-xs font-medium text-white rounded bg-blue-600 hover:bg-blue-700 transition-colors">
                                            {{ __('text.Append to Subject') }}
                                        </button>
                                        <button 
                                            type="button"
                                            onclick="this.blur(); var container = this.closest('.mb-3'); var select = container.querySelector('select'); if(select && select.value) { appendVariableToQuill('confirmation-editor', select.value); @this.call('appendToConfirmationBody'); } return false;"
                                            class="flex-1 px-2 py-1.5 text-xs font-medium text-white rounded bg-blue-600 hover:bg-blue-700 transition-colors">
                                            {{ __('text.Append to Body') }}
                                        </button>
                                    </div>
                                </div>

                                <input
                                    type="text"
                                    wire:model.live="confirmationTitle"
                                    placeholder="{{ __('text.Email Subject') }}"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                @error('confirmationTitle') <span class="text-red-500 text-sm mt-1 block mb-3">{{ $message }}</span> @enderror
                                <div wire:ignore class="@if(!$errors->has('confirmationTitle')) mt-3 @endif">
                                    <div
                                        id="confirmation-editor"
                                        class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                        style="height: 250px; overflow-y: auto;"></div>
                                </div>
                            </div>

                            {{-- Rescheduling Communication --}}
                            <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 shadow-sm">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{ __('text.Rescheduling Communication') }}
                                </label>
                                
                                {{-- Variable Selector for Rescheduling --}}
                                <div class="mb-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <label class="mb-2 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                        {{ __('text.Select Variable') }}
                                    </label>
                                    <div class="relative">
                                        <select 
                                            wire:model="selectedVariableRescheduling"
                                            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                            <option value="">{{ __('text.Select Variable') }}</option>
                                            @foreach($variables as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mt-2 flex gap-2">
                                        <button 
                                            type="button"
                                            wire:click="appendToReschedulingSubject"
                                            class="flex-1 px-2 py-1.5 text-xs font-medium text-white rounded bg-blue-600 hover:bg-blue-700 transition-colors">
                                            {{ __('text.Append to Subject') }}
                                        </button>
                                        <button 
                                            type="button"
                                            onclick="this.blur(); var container = this.closest('.mb-3'); var select = container.querySelector('select'); if(select && select.value) { appendVariableToQuill('rescheduling-editor', select.value); select.value = ''; @this.set('selectedVariableRescheduling', ''); } return false;"
                                            class="flex-1 px-2 py-1.5 text-xs font-medium text-white rounded bg-blue-600 hover:bg-blue-700 transition-colors">
                                            {{ __('text.Append to Body') }}
                                        </button>
                                    </div>
                                </div>

                                <input
                                    type="text"
                                    wire:model.live="reschedulingTitle"
                                    placeholder="{{ __('text.Email Subject') }}"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                @error('reschedulingTitle') <span class="text-red-500 text-sm mt-1 block mb-3">{{ $message }}</span> @enderror
                                <div wire:ignore class="@if(!$errors->has('reschedulingTitle')) mt-3 @endif">
                                    <div
                                        id="rescheduling-editor"
                                        class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                        style="height: 250px; overflow-y: auto;"></div>
                                </div>
                            </div>

                            {{-- Cancel Communication --}}
                            <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 shadow-sm">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{ __('text.Cancel Communication') }}
                                </label>
                                
                                {{-- Variable Selector for Cancel --}}
                                <div class="mb-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <label class="mb-2 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                        {{ __('text.Select Variable') }}
                                    </label>
                                    <div class="relative">
                                        <select 
                                            wire:model="selectedVariableCancel"
                                            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                            <option value="">{{ __('text.Select Variable') }}</option>
                                            @foreach($variables as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mt-2 flex gap-2">
                                        <button 
                                            type="button"
                                            wire:click="appendToCancelSubject"
                                            class="flex-1 px-2 py-1.5 text-xs font-medium text-white rounded bg-blue-600 hover:bg-blue-700 transition-colors">
                                            {{ __('text.Append to Subject') }}
                                        </button>
                                        <button 
                                            type="button"
                                            onclick="this.blur(); var container = this.closest('.mb-3'); var select = container.querySelector('select'); if(select && select.value) { appendVariableToQuill('cancel-editor', select.value); select.value = ''; @this.set('selectedVariableCancel', ''); } return false;"
                                            class="flex-1 px-2 py-1.5 text-xs font-medium text-white rounded bg-blue-600 hover:bg-blue-700 transition-colors">
                                            {{ __('text.Append to Body') }}
                                        </button>
                                    </div>
                                </div>

                                <input
                                    type="text"
                                    wire:model.live="cancelTitle"
                                    placeholder="{{ __('text.Email Subject') }}"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                @error('cancelTitle') <span class="text-red-500 text-sm mt-1 block mb-3">{{ $message }}</span> @enderror
                                <div wire:ignore class="@if(!$errors->has('cancelTitle')) mt-3 @endif">
                                    <div
                                        id="cancel-editor"
                                        class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                        style="height: 250px; overflow-y: auto;"></div>
                                </div>
                            </div>
                            </div>
                        </div>
                        @endif

                        {{-- SMS Templates Section --}}
                        @if($tab == '1')
                        <div class="w-full px-2.5">
                            <h3 class="text-lg font-semibold dark:text-white/90 mb-4 mt-6">{{ __('text.SMS Templates') }}</h3>

                            {{-- SMS Templates in Column Layout --}}
                            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
                            {{-- Confirmation SMS --}}
                            <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 shadow-sm">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{ __('text.Confirm SMS') }}
                                </label>
                                
                                {{-- Variable Selector for Confirmation SMS --}}
                                <div class="mb-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <label class="mb-2 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                        {{ __('text.Select Variable') }}
                                    </label>
                                    <div class="relative">
                                        <select 
                                            wire:model="selectedVariableConfirmationSms"
                                            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                            <option value="">{{ __('text.Select Variable') }}</option>
                                            @foreach($variables as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mt-2 flex gap-2">
                                        <button 
                                            type="button"
                                            wire:click="appendToConfirmationSms"
                                            onclick="this.blur()"
                                            class="flex-1 px-2 py-1.5 text-xs font-medium text-white rounded bg-blue-600 hover:bg-blue-700 transition-colors">
                                            {{ __('text.Append to Body') }}
                                        </button>
                                    </div>
                                </div>

                                <textarea
                                    wire:model.live="confirmationSms"
                                    placeholder="{{ __('text.SMS Message') }}"
                                    rows="6"
                                    class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                    style="resize: vertical;"></textarea>
                            </div>

                            {{-- Rescheduling SMS --}}
                            <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 shadow-sm">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{ __('text.Reschedule SMS') }}
                                </label>
                                
                                {{-- Variable Selector for Rescheduling SMS --}}
                                <div class="mb-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <label class="mb-2 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                        {{ __('text.Select Variable') }}
                                    </label>
                                    <div class="relative">
                                        <select 
                                            wire:model="selectedVariableReschedulingSms"
                                            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                            <option value="">{{ __('text.Select Variable') }}</option>
                                            @foreach($variables as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mt-2 flex gap-2">
                                        <button 
                                            type="button"
                                            wire:click="appendToReschedulingSms"
                                            class="flex-1 px-2 py-1.5 text-xs font-medium text-white rounded bg-blue-600 hover:bg-blue-700 transition-colors">
                                            {{ __('text.Append to Body') }}
                                        </button>
                                    </div>
                                </div>

                                <textarea
                                    wire:model.live="reschedulingSms"
                                    placeholder="{{ __('text.SMS Message') }}"
                                    rows="6"
                                    class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                    style="resize: vertical;"></textarea>
                            </div>

                            {{-- Cancel SMS --}}
                            <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 shadow-sm">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{ __('text.Cancel SMS') }}
                                </label>
                                
                                {{-- Variable Selector for Cancel SMS --}}
                                <div class="mb-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <label class="mb-2 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                        {{ __('text.Select Variable') }}
                                    </label>
                                    <div class="relative">
                                        <select 
                                            wire:model="selectedVariableCancelSms"
                                            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                            <option value="">{{ __('text.Select Variable') }}</option>
                                            @foreach($variables as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mt-2 flex gap-2">
                                        <button 
                                            type="button"
                                            wire:click="appendToCancelSms"
                                            class="flex-1 px-2 py-1.5 text-xs font-medium text-white rounded bg-blue-600 hover:bg-blue-700 transition-colors">
                                            {{ __('text.Append to Body') }}
                                        </button>
                                    </div>
                                </div>

                                <textarea
                                    wire:model.live="cancelSms"
                                    placeholder="{{ __('text.SMS Message') }}"
                                    rows="6"
                                    class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                    style="resize: vertical;"></textarea>
                            </div>
                            </div>
                        </div>
                        @endif

                        {{-- Attachments Section --}}
                        @if($tab == '1')
                        <div class="w-full px-2.5">
                            <h3 class="text-lg font-semibold dark:text-white/90 mb-4 mt-6">{{ __('text.Attachments') }}</h3>
                            
                            <div class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="flex gap-3 items-end">
                                    <div class="flex-1">
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            {{ __('text.Select File') }}
                                        </label>
                                        <input 
                                            type="file" 
                                            wire:model="attachmentFile" 
                                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.rtf,.odt,.ods,.odp"
                                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                        @error('attachmentFile') 
                                            <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> 
                                        @enderror
                                    </div>
                                    <div>
                                        <button 
                                            type="button" 
                                            wire:click="addAttachment"
                                            class="px-4 py-2.5 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 transition-colors">
                                            {{ __('text.Add Attachment') }}
                                        </button>
                                    </div>
                                </div>

                                {{-- List of Attachments --}}
                                @if(count($attachments) > 0)
                                <div class="mt-4 space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-2">
                                        {{ __('text.Attached Files') }}
                                    </label>
                                    @foreach($attachments as $index => $attachment)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center gap-3 flex-1">
                                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $attachment['name'] ?? 'File' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if(isset($attachment['path']))
                                            <a 
                                                href="{{ Storage::disk('public')->url($attachment['path']) }}" 
                                                target="_blank"
                                                class="p-2 text-gray-600 dark:text-gray-400 hover:text-brand-500 dark:hover:text-brand-400 transition-colors"
                                                title="{{ __('text.View') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            @endif
                                            <button 
                                                type="button" 
                                                wire:click="removeAttachment({{ $index }})"
                                                class="p-2 text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors"
                                                title="{{ __('text.Remove') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                    </div>
                    <div class="w-full xl:w-1/2 flex gap-3">
                        <button type="submit" onclick="syncQuillContent(); return true;" class="text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 primary-btn py-2 px-3 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2">
                            {{ $isEdit ? __('text.Update') : __('text.Create') }}
                        </button>
                        <a href="{{ url('category-management') }}" class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-error-500 shadow-theme-xs hover:bg-error-600">
                            {{ __('text.Cancel') }}</a>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('js/cdn/sweetalert2.js') }}"></script>
<link href="{{ asset('css/cdn/quill.snow.css') }}" rel="stylesheet">
<script src="{{ asset('js/cdn/quill.min.js') }}"></script>

<script>
    // Store Quill instances globally
    window.quillInstances = window.quillInstances || {};

    function initQuillEditor(editorId, propertyName) {
        let editorElement = document.getElementById(editorId);
        if (!editorElement) {
            return;
        }

        // Check if Quill is already initialized on this element
        if (editorElement._quill && typeof editorElement._quill.root !== 'undefined') {
            // Quill already initialized, just update content if needed
            let content = @this.get(propertyName) || '';
            if (content && editorElement._quill.root.innerHTML !== content) {
                editorElement._quill.root.innerHTML = content;
            }
            return;
        }

        // Clean up any existing Quill instance
        if (window.quillInstances[editorId]) {
            try {
                if (window.quillInstances[editorId].root) {
                    window.quillInstances[editorId] = null;
                }
            } catch (e) {
                // Ignore cleanup errors
            }
        }

        // Clear the element
        editorElement.innerHTML = '';

        try {
            let quill = new Quill('#' + editorId, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{
                            'header': [1, 2, 3, false]
                        }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        [{
                            'align': []
                        }],
                        ['link', 'image'],
                        ['clean']
                    ]
                }
            });

            // Store the instance
            window.quillInstances[editorId] = quill;
            editorElement._quill = quill;

            // Set initial content
            let content = @this.get(propertyName) || '';
            if (content) {
                quill.root.innerHTML = content;
            }

            // Watch for Livewire property changes and update Quill
            let lastContent = content;
            setInterval(() => {
                let currentContent = @this.get(propertyName) || '';
                if (currentContent !== lastContent && currentContent !== quill.root.innerHTML) {
                    // Only update if not programmatically inserting
                    if (!quill._isProgrammaticInsert) {
                        quill.root.innerHTML = currentContent;
                        lastContent = currentContent;
                    }
                }
            }, 200);

            // Update Livewire property on content change (with shorter debounce)
            // Skip sync if this is a programmatic insertion
            let updateTimeout;
            let isUpdating = false;
            quill.on('text-change', function(delta, oldDelta, source) {
                // Skip sync if this is a programmatic insertion or if we're already updating
                if (quill._isProgrammaticInsert || isUpdating || source === 'api') {
                    return;
                }
                clearTimeout(updateTimeout);
                updateTimeout = setTimeout(function() {
                    if (!quill._isProgrammaticInsert) {
                        isUpdating = true;
                        lastContent = quill.root.innerHTML;
                        @this.set(propertyName, quill.root.innerHTML);
                        setTimeout(() => { isUpdating = false; }, 50);
                    }
                }, 100);
            });

            // Also sync on blur to ensure content is saved (but skip if programmatic)
            quill.root.addEventListener('blur', function() {
                if (!quill._isProgrammaticInsert) {
                    @this.set(propertyName, quill.root.innerHTML);
                }
            });
        } catch (e) {
            console.error('Error initializing Quill editor for ' + editorId + ':', e);
        }
    }

    function initQuillEditors() {
        // Confirmation email editor - QuillEditor
        initQuillEditor('confirmation-editor', 'confirmationContent');

        // Rescheduling email editor - QuillEditor
        initQuillEditor('rescheduling-editor', 'reschedulingContent');

        // Cancel email editor - QuillEditor
        initQuillEditor('cancel-editor', 'cancelContent');

        // Description editor for packages - QuillEditor (check if element exists)
        const descriptionEditorEl = document.getElementById('description-editor');
        if (descriptionEditorEl && !descriptionEditorEl._quill) {
            try {
                initQuillEditor('description-editor', 'description');
            } catch(e) {
                console.error('Error initializing description editor:', e);
            }
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initQuillEditors, 200);
            // Also try after a longer delay to catch any late-rendered elements
            setTimeout(initQuillEditors, 500);
        });
    } else {
        setTimeout(initQuillEditors, 200);
        // Also try after a longer delay
        setTimeout(initQuillEditors, 500);
    }

    // Initialize when Livewire is ready
    document.addEventListener('livewire:init', function() {
        setTimeout(function() {
            initQuillEditors();
        }, 200);

        // Listen for append-to-editor event (Livewire 3 format)
        // Remove any existing listener first to prevent duplicates (if method exists)
        if (typeof Livewire !== 'undefined' && typeof Livewire.off === 'function') {
            Livewire.off('append-to-editor');
        }
        
        Livewire.on('append-to-editor', (data) => {
            // Handle both array and object formats
            let editorId, text;
            if (Array.isArray(data)) {
                editorId = data[0]?.editor || data.editor;
                text = data[0]?.text || data.text;
            } else if (data && typeof data === 'object') {
                editorId = data.editor;
                text = data.text;
            } else {
                return;
            }
            
            if (!editorId || !text) {
                return;
            }
            
            // Wait a bit to ensure Quill is ready
            setTimeout(() => {
                if (!window.quillInstances || !window.quillInstances[editorId]) {
                    return;
                }
                
                const quill = window.quillInstances[editorId];
                
                // Prevent multiple insertions with a flag
                if (quill._isAppending) {
                    return;
                }
                quill._isAppending = true;
                
                try {
                    const range = quill.getSelection(true);
                    const index = range ? range.index : (quill.getLength() > 1 ? quill.getLength() - 1 : 0);
                    
                    // Set flag to prevent sync during programmatic insertion
                    quill._isProgrammaticInsert = true;
                    
                    // Insert text at cursor position (only once)
                    quill.insertText(index, text);
                    quill.setSelection(index + text.length);
                    
                    // Update Livewire property after insertion
                    setTimeout(() => {
                        const propertyMap = {
                            'confirmation-editor': 'confirmationContent',
                            'rescheduling-editor': 'reschedulingContent',
                            'cancel-editor': 'cancelContent',
                            'description-editor': 'description'
                        };
                        const propertyName = propertyMap[editorId];
                        if (propertyName) {
                            @this.set(propertyName, quill.root.innerHTML);
                        }
                        // Clear flags after update
                        quill._isProgrammaticInsert = false;
                        quill._isAppending = false;
                    }, 150);
                } catch (e) {
                    console.error('Error inserting text:', e);
                    quill._isAppending = false;
                    quill._isProgrammaticInsert = false;
                }
            }, 50);
        });

        Livewire.hook('message.processed', (message, component) => {
            // Reinitialize after Livewire updates
            setTimeout(function() {
                initQuillEditors();
                // Specifically check for description editor after updates
                const descEditor = document.getElementById('description-editor');
                if (descEditor && !descEditor._quill) {
                    initQuillEditor('description-editor', 'description');
                }
            }, 200);
        });
    });

    // Function to append variable directly to Quill editor (global function)
    window.appendVariableToQuill = function(editorId, text) {
        if (!text || !editorId) {
            return;
        }
        
        setTimeout(() => {
            if (!window.quillInstances || !window.quillInstances[editorId]) {
                console.log('Quill instance not found:', editorId);
                return;
            }
            
            const quill = window.quillInstances[editorId];
            
            // Prevent multiple insertions
            if (quill._isAppending) {
                return;
            }
            quill._isAppending = true;
            
            try {
                const range = quill.getSelection(true);
                const index = range ? range.index : (quill.getLength() > 1 ? quill.getLength() - 1 : 0);
                
                // Set flag to prevent sync during programmatic insertion
                quill._isProgrammaticInsert = true;
                
                // Insert text at cursor position
                quill.insertText(index, ' ' + text);
                quill.setSelection(index + text.length + 1);
                
                // Update Livewire property
                setTimeout(() => {
                    const propertyMap = {
                        'confirmation-editor': 'confirmationContent',
                        'rescheduling-editor': 'reschedulingContent',
                        'cancel-editor': 'cancelContent'
                    };
                    const propertyName = propertyMap[editorId];
                    if (propertyName) {
                        @this.set(propertyName, quill.root.innerHTML);
                    }
                    // Clear flags
                    quill._isProgrammaticInsert = false;
                    quill._isAppending = false;
                }, 150);
            } catch (e) {
                console.error('Error inserting text:', e);
                quill._isAppending = false;
                quill._isProgrammaticInsert = false;
            }
        }, 50);
    };;

    // Sync QuillEditor content before form submission
    function syncQuillContent() {
        if (window.quillInstances['confirmation-editor']) {
            @this.set('confirmationContent', window.quillInstances['confirmation-editor'].root.innerHTML);
        }
        if (window.quillInstances['rescheduling-editor']) {
            @this.set('reschedulingContent', window.quillInstances['rescheduling-editor'].root.innerHTML);
        }
        if (window.quillInstances['cancel-editor']) {
            @this.set('cancelContent', window.quillInstances['cancel-editor'].root.innerHTML);
        }
        // Sync description editor if it exists
        if (window.quillInstances['description-editor']) {
            @this.set('description', window.quillInstances['description-editor'].root.innerHTML);
        }
    }
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        $(document).ready(function() {
            $('#locations-select').select2();
            $('#locations-select').on("change", function(e) {
                let data = $(this).val();
                @this.set('locations', data);
            });
        });
        
    });
    
</script>
</div>