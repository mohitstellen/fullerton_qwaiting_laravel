<div class="p-6">
    <style>
        .stats-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        
    </style>
    <h2 class="text-xl font-semibold mb-4">{{ __('report.Feedback Report') }}</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="stats-card">
            <div class="stats-heading mb-3">{{ __('report.Total Queue') }}</div>
            <div class="stats-value text-4xl font-semibold" id="totalQueue">{{ $cardsDetails['totalQueue'] ?? 0 }}</div>
        </div>

        <div class="stats-card">
            <div class="stats-heading mb-3">{{ __('report.Closed Queue') }}</div>
            <div class="stats-value text-4xl font-semibold" id="closedQueue">{{ $cardsDetails['closedQueue'] ?? 0 }}</div>
        </div>

        <div class="stats-card">
            <div class="stats-heading mb-3">{{ __('report.Average Rating') }}</div>
            <div class="stats-value text-4xl font-semibold" id="averageRating">{{ $cardsDetails['averageRating'] ?? 0 }}</div>
        </div>
    </div>


    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 items-end">
        <div class="flex-col">
            <label for="createdFrom">{{ __('report.From Date') }}</label>
            <input 
                type="date" 
                wire:model.live="createdFrom" 
                onclick="this.showPicker()"
                class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full"
            />
        </div>
        <div class="flex-col">
            <label for="createdUntil">{{ __('report.To Date') }}</label>
            <input 
                type="date" 
                wire:model.live="createdUntil" 
                onclick="this.showPicker()"
                class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full"
            />
        </div>
        <div class="flex-col">
        <!-- <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search feedback..." class="input" /> -->
        </div>
        <div class="items-end flex justify-end md:text-right">
            <button 
                wire:click="exportcsv" 
                class="text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 primary-btn"
            ><i class="ri-file-list-3-line"></i>
                {{ __('report.Export CSV') }}
            </button>
            <button 
        wire:click="exportpdf"
        class="ml-2 text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 secondry-btn"
    ><i class="ri-file-pdf-2-line"></i>
        {{ __('report.Export PDF') }}
    </button>
        </div>
    </div>

    <div>
    
    <div class="overflow-x-auto bg-white shadow-md rounded-lg p-4">    
    <table  class="table-auto w-full ti-custom-table ti-custom-table-hover">
        <thead>
            <tr>
                {{-- <th class=" p-2">{{ __('report.Location') }}</th>
                <th class=" p-2">{{ __('report.Domain') }}</th> --}}
                <th class=" p-2">{{ __('report.name') }}</th>
                <th class=" p-2">{{ __('report.Question') }}</th>
                <th class=" p-2">{{ __('report.Rating') }}</th>
            </tr>
        </thead>
        <tbody>
           @if(count($reports) > 0)
            @foreach($reports as $report)
                <tr>
                    {{-- <td class=" p-2">{{ $report->queues->location->location_name ?? 'N/A' }}</td>
                    <td class=" p-2">{{ $domain ?? 'N/A' }}</td> --}}
                    <td class=" p-2">
                    @php
                        $name = !empty($report->user_id) ? $report->user->name : ($report->queues->name ?? 'N/A');
                    @endphp
                    {{ $name }}
                    </td>
                    <td class=" p-2">{{ $report->question ?? 'N/A' }}</td>
                    <td class=" p-2">{{ \App\Models\Queue::getEmojiText()[$report->rating]['emoji'] ?? 'N/A' }}</td>
                </tr>
            @endforeach
            @else
            <tr>
                <td colspan="7" class="text-center py-6">
                    <p class="text-center"><strong>{{ __('report.No records found.') }}</strong></p>
                </td>
            </tr>
            @endif
         
        </tbody>
    </table>
    </div>

    <div class="mt-4">
        {{ $reports->links() }}
    </div>
</div>
