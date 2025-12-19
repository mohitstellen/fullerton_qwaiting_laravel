<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-6">
    <h2 class="text-xl font-semibold mb-4">{{ __('setting.Edit Location') }}</h2>

    <form wire:submit.prevent="updateLocation">

        <div class="mb-4">
            <label class="block text-gray-700">{{ __('setting.Name') }}*</label>
            <input type="text" id="location-name-autocomplete" wire:model="location_name" class="w-full p-2 border rounded">
            @error('location_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">{{ __('setting.Address') }}</label>
            <input type="text" id="address-input" wire:model="address" class="w-full p-2 border rounded">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Map Link</label>
            <input type="text" wire:model="map_link" placeholder="https://maps.google.com/..." class="w-full p-2 border rounded">
            @error('map_link') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Phone Number</label>
            <input type="text" wire:model="phone_number" placeholder="Phone Number" class="w-full p-2 border rounded">
            @error('phone_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Remarks</label>
            <textarea wire:model="remarks" placeholder="Remarks" rows="3" class="w-full p-2 border rounded"></textarea>
            @error('remarks') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">SMS Number</label>
            <input type="text" wire:model="sms_number" placeholder="SMS Number" class="w-full p-2 border rounded">
            @error('sms_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
                @error('latitude') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-gray-700">{{ __('setting.Longitude') }}*</label>
                <input type="text" wire:model="longitude" id="longitude" class="w-full p-2 border rounded">
                @error('longitude') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

          <div class="mb-4">
            <label class="block text-gray-700">Clinic Image</label>
            <input type="file" wire:model="location_image" class="w-full p-2 border rounded">
            @error('location_image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

            @if ($location_image)
                <p class="mt-2 text-sm text-gray-600">Preview:</p>
                <img src="{{ $location_image->temporaryUrl() }}" class="w-32 h-32 object-cover mt-2 rounded-lg">
            @elseif ($existing_image)
                <p class="mt-2 text-sm text-gray-600">Current Image:</p>
                <img src="{{ url('storage/' . $existing_image) }}" class="w-32 h-32 object-cover mt-2 rounded-lg">
            @endif
        </div>

    <div class="grid grid-cols-2 gap-4 mt-4">
    @php
        $baseId = base64_encode($location_id);
        $fullUrl = route('single-display', $baseId);
    @endphp
    <div>
        <label class="block text-gray-700">Single Display URL</label>
        <div class="flex items-center gap-2">
            <!-- Input with full URL -->
            <input type="text"
                   class="w-full p-2 border rounded"
                   value="{{ $fullUrl }}"
                   readonly
                   id="singleDisplayUrl">

            <!-- Copy button -->
            <button type="button"
                    class="px-3 py-2 text-white bg-blue-600 rounded relative"
                    onclick="copyUrl()">
                Copy
            </button>

            <!-- Copied text -->
            <span id="copyMsg" class="text-green-600 text-sm hidden">Copied!</span>
        </div>
    </div>
</div>

        <div class="grid grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-gray-700">Available for Public Booking</label>
                <select wire:model="available_for_public_booking" class="w-full p-2 border rounded">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
                @error('available_for_public_booking') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="status" id="editStatus" class="h-4 w-4">
                <label for="editStatus" class="text-gray-700">{{ __('setting.Active') }}</label>
            </div>
        </div>

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


<script>
    document.addEventListener('livewire:init', function () {
        let isEditAutocompleteBound = false;

        const bindEditAutocomplete = () => {
            if (isEditAutocompleteBound) {
                return true;
            }

            const nameInput = document.getElementById('location-name-autocomplete');
            if (!nameInput || typeof google === 'undefined' || !google.maps || !google.maps.places) {
                return false;
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

            isEditAutocompleteBound = true;
            return true;
        };

        if (!bindEditAutocomplete()) {
            let attempts = 0;
            const maxAttempts = 20;
            const retryInterval = setInterval(() => {
                attempts++;
                if (bindEditAutocomplete() || attempts >= maxAttempts) {
                    clearInterval(retryInterval);
                }
            }, 300);
        }
    });

     function copyUrl() {
        const input = document.getElementById('singleDisplayUrl');
        navigator.clipboard.writeText(input.value).then(() => {
            const msg = document.getElementById('copyMsg');
            msg.classList.remove('hidden');
            setTimeout(() => msg.classList.add('hidden'), 2000); // hide after 2 sec
        });
    }
</script>

<!-- Load Google Places API -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_PLACES_API_KEY') }}&libraries=places"></script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
     document.addEventListener("DOMContentLoaded", function () {

    Livewire.on('updated', () => {
        Swal.fire({
            title: 'Success!',
            text: 'Clinic updated successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then((result) => {
            // if (result.isConfirmed) {
                window.location.href = '/locations'; // Refresh the page when OK is clicked
            // }
        });
    });
    });
</script>
