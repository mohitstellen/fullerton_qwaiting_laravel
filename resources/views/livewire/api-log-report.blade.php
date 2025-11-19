<div class="p-6">
    <div class="flex items-center gap-2 mb-6 text-sm">
        <a href="{{ route('tenant.all-report') }}" class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white">
            {{ __('sidebar.All Reports') }}
        </a>
        <span class="text-gray-400">/</span>
        <span class="font-semibold text-gray-900 dark:text-white">API Logs</span>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-1 mb-6">
        <h2 class="text-xl font-semibold dark:text-white">API Logs</h2>
    </div>

    <div class="flex items-center space-x-4 mb-4 py-2 flex-wrap gap-4">
        <div>
            <label for="fromDate" class="mr-2">{{ __('report.From') }}:</label>
            <input type="date" onclick="this.showPicker()" wire:model.live="fromSelectedDate" id="fromDate" class="border border-gray-300 rounded p-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
        </div>
        <div>
            <label for="toDate" class="mr-2">{{ __('report.To') }}:</label>
            <input type="date" onclick="this.showPicker()" wire:model.live="toSelectedDate" id="toDate" class="border border-gray-300 rounded p-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
        </div>
        <div>
            <label for="statusFilter" class="mr-2">Status:</label>
            <select wire:model.live="statusFilter" id="statusFilter" class="border border-gray-300 rounded p-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                <option value="all">All</option>
                <option value="success">Success</option>
                <option value="error">Error</option>
            </select>
        </div>
        <div>
            <label for="searchTerm" class="mr-2">Search:</label>
            <input type="text" wire:model.live="searchTerm" id="searchTerm" placeholder="Search API logs..." class="border border-gray-300 rounded p-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
        </div>
    </div>

    <div class="overflow-x-auto bg-white shadow-md rounded-lg dark:bg-gray-800">
        <table class="table-auto w-full ti-custom-table ti-custom-table-hover">
            <thead>
                <tr>
                    <th class="p-2">Date/Time</th>
                    <th class="p-2">Booking ID</th>
                    <th class="p-2">API Name</th>
                    <th class="p-2">Status</th>
                    <th class="p-2">HTTP Code</th>
                    <th class="p-2">Error</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr class="{{ $log->status === 'error' ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                        <td class="p-2 text-sm">{{ $log->created_at->format($datetimeFormat) }}</td>
                        <td class="p-2 text-sm">
                            @if($log->booking_id)
                                <span class="font-medium">#{{ $log->booking_id }}</span>
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="p-2 text-sm">
                            <span class="px-2 py-1 rounded text-xs font-medium {{ $log->api_name === 'Organization List' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                {{ $log->api_name }}
                            </span>
                        </td>
                        <td class="p-2 text-sm">
                            <span class="px-2 py-1 rounded text-xs font-medium {{ $log->status === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                        <td class="p-2 text-sm">
                            <span class="px-2 py-1 rounded text-xs font-mono {{ $log->http_code >= 200 && $log->http_code < 300 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $log->http_code ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="p-2 text-sm">
                            @if($log->error_message)
                                <span class="text-red-600 text-xs">{{ Str::limit($log->error_message, 50) }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-6">
                            <p class="text-center dark:text-gray-400"><strong>{{ __('report.No records found.') }}</strong></p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>