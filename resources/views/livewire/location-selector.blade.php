<?php use Illuminate\Support\Facades\Session; ?>

@php
    $user = auth()->user();
    $isAdmin = $user && $user->hasRole('Admin');
    $accessibleLocations = [];

    if ($isAdmin || !$user) {
        $accessibleLocations = $locations;
    } elseif (!is_null($user->locations)) {
        foreach ($locations as $key => $location) {
            if (in_array($key, $user->locations)) {
                $accessibleLocations[$key] = $location;
            }
        }
    }
@endphp

<div wire:ignore x-data="{ 
    open: false, 
    selected: @entangle('selectedLocationUser'),
    locations: {{ Js::from($accessibleLocations) }},
    getLocationName(key) {
        return this.locations[key] || '{{ __('text.select location') }}';
    }
}" @click.away="open = false" class="relative">
    @if(count($accessibleLocations) > 1)
        <div @click="open = !open" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 cursor-pointer truncate flex justify-between items-center">
            <span class="truncate" x-text="getLocationName(selected)"></span>
            <svg class="w-4 h-4 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
        
        <div x-show="open" 
             x-transition
             class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto dark:bg-gray-700 dark:border-gray-600">
            @foreach($accessibleLocations as $key => $location)
                <div @click="open = false; $wire.set('selectedLocationUser', '{{ $key }}').then(() => { window.location.reload(); });" 
                     class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer text-sm text-gray-900 dark:text-white">
                    {{ $location }}
                </div>
            @endforeach
        </div>
        
    @elseif(count($accessibleLocations) == 1)
        @foreach($accessibleLocations as $location)
            <div class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 truncate">
                <b>{{ $location }}</b>
            </div>
        @endforeach
    @endif
</div>


