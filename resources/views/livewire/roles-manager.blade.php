<div class="p-4">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-semibold dark:text-white/90">{{ __('text.Roles') }}</h2>
    </div>

    <!-- Tabs -->
    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
        <nav class="flex space-x-8" aria-label="Tabs">
            <a href="{{ route('tenant.create-role') }}" class="border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                {{ __('text.New Role') }}
            </a>
            <a href="{{ route('tenant.roles') }}" class="border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-600 dark:text-blue-400">
                {{ __('text.Role List') }}
            </a>
        </nav>
    </div>

    <!-- Search Box -->
    <div class="mb-4 flex gap-3 justify-between">
        <div class="relative w-full lg:w-[300px]">
            <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/4">
                <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z"
                        fill="" />
                </svg>
            </span>
            <input type="text" wire:model.live="search" placeholder="Search Role Name"
                class="bg-white dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
        </div>
        <button wire:click="$refresh" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
        </button>
    </div>

    <div class="p-4 md:p-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow">
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-auto">
                <thead>
                    <tr class="bg-blue-50 dark:bg-gray-800">
                        <th class="px-5 py-3 sm:px-6 text-left text-xs font-medium text-white uppercase tracking-wider">
                            S.No
                        </th>
                        <th class="px-5 py-3 sm:px-6 text-left text-xs font-medium text-white uppercase tracking-wider">
                            {{ __('text.Role Name') }}
                        </th>
                        <th class="px-5 py-3 sm:px-6 text-left text-xs font-medium text-white uppercase tracking-wider">
                            {{ __('text.Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                    @if(count($roles) > 0)
                        @foreach ($roles as $index => $role)
                            <tr class="{{ $loop->even ? 'bg-gray-50 dark:bg-gray-800' : 'bg-white dark:bg-gray-900' }}">
                                <td class="px-5 py-4 sm:px-6 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ ($roles->currentPage() - 1) * $roles->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-5 py-4 sm:px-6 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    <div class="flex items-center">
                                        {{ ucfirst($role->name) }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6 whitespace-nowrap text-sm font-medium">
                                    @can('Role Edit')
                                        <a href="{{ route('tenant.edit-role', $role->id) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3" class="text-center py-6">
                                <img src="{{ url('images/no-record.jpg') }}" alt="No Records Found"
                                    class="mx-auto" style="max-width:300px">
                                <p class="text-gray-500 dark:text-gray-400 mt-2">No records found.</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $roles->links() }}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {

        Livewire.on('confirm-delete', () => {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('confirmed-delete');
                }
            });
        });

        Livewire.on('deleted', () => {
            Swal.fire({
                title: 'Success!',
                text: 'Deleted successfully.',
                icon: 'success',
                // confirmButtonText: 'OK'
            })

        });
    });
    </script>
