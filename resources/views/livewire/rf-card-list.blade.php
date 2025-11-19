<div class="space-y-4 m-2">
    <div class="flex flex-wrap items-center justify-between gap-1 mb-6">
        <h2 class="text-xl font-semibold dark:text-white">{{ __('ID Badge') }}</h2>
    </div>

    <div class="mb-4 flex gap-3 justify-between mb-4 flex-wrap">
      

            <div  class="relative w-full lg:w-[300px]">
                <span class="pointer-events-none absolute top-1/3 left-4 -translate-y-1/4">
                    <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z"
                            fill="" />
                    </svg>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('text.Search') }}..."
                    class="dark:bg-dark-900 bg-white shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
            </div>
            <div class="flex gap-x-3">
             <div>
            SHOW
            <select wire:model="perPage" class="border rounded p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>

</div>
        </div>

    <div class="p-4 bg-white shadow-md rounded-lg dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="table-auto w-full ti-custom-table ti-custom-table-hover">
                <thead>
                    <tr>
                        <th class="px-5 py-3 sm:px-6">ID Badge</th>
                        <th class="px-5 py-3 sm:px-6">Username</th>
                        <th class="px-5 py-3 sm:px-6">Created</th>
                        <th class="px-5 py-3 sm:px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rfCards as $card)
                        <tr>
                            <td class="px-5 py-4 sm:px-6">{{ $card->rfcard }}</td>
                            <td class="px-5 py-4 sm:px-6">{{ $card->username }}</td>
                            <td class="px-5 py-4 sm:px-6">{{ $card->created_at->format('d-m-Y') }}</td>
                           
                              <td class="px-5 py-4 sm:px-6">
                            <div class="">
                                <div x-data="{openDropDown: false}" class="relative">
                                    <button @click="openDropDown = !openDropDown" class="text-gray-500 dark:text-gray-400 action-btn">
                                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z" fill=""></path>
                                        </svg>
                                    </button>
                                    <div x-show="openDropDown" @click.outside="openDropDown = false" class="dropdown-menu shadow-theme-lg dark:bg-gray-dark absolute top-full left-0 z-40 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800" style="display: none;">
                                  
                                      
                                        <button class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300" wire:click="confirmDelete({{ $card->id }})">
                                            {{ __('text.Delete') }}
                                        </button>
                                      
                                    </div>
                                </div>
                            </div>

                        </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-4 text-center">No RF Cards found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $rfCards->links() }}
        </div>
    </div>

  
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
            Livewire.on('rf-confirm', (data) => {
                Swal.fire({
                    title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('confirmed-rf-delete');
                    }
                });
            });

            Livewire.on('rf-notify', (data) => {
                Swal.fire({
                    title: 'Success!',
                    text: 'Deleted successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            });
        });
    </script>
   
</div>
