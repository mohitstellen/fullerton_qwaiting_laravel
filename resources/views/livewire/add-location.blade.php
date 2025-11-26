<div class="p-4">
    <h2 class="text-xl font-semibold mb-4">{{ __('setting.Add Location') }}</h2>

    <div class="rounded bg-white shadow p-4 dark:border-gray-800 dark:bg-white/[0.03]">
    <form wire:submit.prevent="save">
        <!-- Address Autocomplete -->

        <div class="mb-4">
           <label class="block text-gray-700">{{ __('setting.Name') }}*</label>
           <input type="text" id="location-name-autocomplete" wire:model="location_name" class="w-full p-2 border rounded">
           @error('location_name') <span class="text-error-500 text-sm">{{ $message }}</span> @enderror
       </div>

        <div class="mb-4">
            <label class="block text-gray-700">{{ __('setting.Address') }}</label>
            <input type="text" id="address-input" wire:model="address" class="w-full p-2 border rounded">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Map Link</label>
            <input type="text" wire:model="map_link" placeholder="https://maps.google.com/..." class="w-full p-2 border rounded">
            @error('map_link') <span class="text-error-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700">{{ __('setting.City') }}</label>
                <input type="text" wire:model="city" id="city" class="w-full p-2 border rounded">
            </div>
            <div>
                <label class="block text-gray-700">{{ __('setting.State') }}</label>
                <input type="text" wire:model="state" id="state" class="w-full p-2 border rounded">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-gray-700">{{ __('setting.Country') }}</label>
                <input type="text" wire:model="country" id="country" class="w-full p-2 border rounded">
            </div>
            <div>
                <label class="block text-gray-700">{{ __('setting.ZIP Code') }}</label>
                <input type="text" wire:model="zip" id="zip" class="w-full p-2 border rounded">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-gray-700">{{ __('setting.Latitude') }}*</label>
                <input type="text" wire:model="latitude" id="latitude" class="w-full p-2 border rounded">
                @error('latitude') <span class="text-error-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-gray-700">{{ __('setting.Longitude') }}*</label>
                <input type="text" wire:model="longitude" id="longitude" class="w-full p-2 border rounded">
                @error('longitude') <span class="text-error-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

         <div class="grid grid-cols-2 gap-4 mt-4">
            <label class="block text-gray-700">Clinic Image</label>
            <input type="file" wire:model="location_image" class="w-full p-2 border rounded">
            @error('location_image') <span class="text-error-500 text-sm">{{ $message }}</span> @enderror

            @if ($location_image)
                <img src="{{ $location_image->temporaryUrl() }}" class="mt-2 h-20 rounded border">
            @endif
        </div>

        <div class="grid grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-gray-700">Available for Public Booking</label>
                <select wire:model="available_for_public_booking" class="w-full p-2 border rounded">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
                @error('available_for_public_booking') <span class="text-error-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="status" id="status" class="h-4 w-4">
                <label for="status" class="text-gray-700">{{ __('setting.Active') }}</label>
            </div>
        </div>

        <input type="hidden" wire:model="ip_address">

        <button type="submit" class="mt-4 px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
            {{ __('setting.Save') }}
        </button>
        <a href="{{ route('tenant.locations') }}" class="mt-4 px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
           {{ __('setting.Cancel') }}
        </a>
    </form>

    @if (session()->has('message'))
        <p class="mt-4 text-green-600">{{ session('message') }}</p>
    @endif
</div>
</div>


<script>
    document.addEventListener('livewire:init', function () {
        const nameInput = document.getElementById('location-name-autocomplete');

        if (!nameInput || typeof google === 'undefined' || !google.maps || !google.maps.places) {
            return;
        }

        const autocomplete = new google.maps.places.Autocomplete(nameInput, {
            fields: ['address_components', 'geometry', 'formatted_address', 'name']
        });

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (!place) {
                return;
            }

            const placeName = place.name || nameInput.value || '';
            if (placeName) {
                nameInput.value = placeName;
                @this.set('location_name', placeName);
            }

            if (place.formatted_address) {
                @this.set('address', place.formatted_address);
            }

            if (Array.isArray(place.address_components)) {
                place.address_components.forEach(function(component) {
                    let types = component.types || [];
                    if (types.includes('locality')) {
                        @this.set('city', component.long_name);
                    }
                    if (types.includes('administrative_area_level_1')) {
                        @this.set('state', component.long_name);
                    }
                    if (types.includes('country')) {
                        @this.set('country', component.long_name);
                    }
                    if (types.includes('postal_code')) {
                        @this.set('zip', component.long_name);
                    }
                });
            }

            if (place.geometry && place.geometry.location) {
                @this.set('latitude', place.geometry.location.lat());
                @this.set('longitude', place.geometry.location.lng());
            }
        });
    });
</script>

<!-- Load Google Places API -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_PLACES_API_KEY') }}&libraries=places"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
     document.addEventListener("DOMContentLoaded", function () {

    Livewire.on('created', () => {
        Swal.fire({
            title: 'Success!',
            text: 'Clinic created successfully.',
            icon: 'success',
            // confirmButtonText: 'OK'
        }).then((result) => {
            // if (result.isConfirmed) {
                window.location.href = '/locations'; // Refresh the page when OK is clicked
            // }
        });
    });
    });
</script>
