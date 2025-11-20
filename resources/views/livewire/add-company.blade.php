<div class="p-4">
    <div class="space-y-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Add Company</h2>
        <a href="{{ route('tenant.companies.index') }}"
           class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
            Back to list
        </a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <form wire:submit.prevent="save" class="space-y-6">
            @include('livewire.company-form')

            <div class="flex items-center justify-end gap-3">
                <button type="submit"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                    Save
                </button>
            </div>
        </form>
    </div>
    </div>
</div>