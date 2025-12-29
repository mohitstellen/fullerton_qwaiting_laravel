<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StripeResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;


class PaymentReport extends Component
{
    use WithPagination;

    public $team_id;
   public $location_id;
    public $startDate;
    public $endDate;

    public $totalRevenue = 0;
    public $avgRevenue = 0;
    public $lastPaymentDate;
    public $lastPaymentAmount;
    public $categoryRevenue = [];
    public $revenueOverTime = [];

    protected $paginationTheme = 'tailwind';

    public function updatingStartDate() { $this->resetPage(); $this->dispatch('updatedchart');}
    public function updatingEndDate() { $this->resetPage(); $this->dispatch('updatedchart');}

    public function mount()
    {
        $this->team_id = tenant('id');
        $this->location_id = Session::get('selectedLocation');
        $this->startDate = now()->subDays(7)->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function render()
    {
        $query = StripeResponse::query()
        ->where('team_id',$this->team_id)->where('location_id', $this->location_id)
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->where('status', 'succeeded');

            // dd($query->get());

            $allTransactions = (clone $query)->get();
        $paginated = $query->latest()->paginate(4); // ✅ Paginated

        // Use full dataset for stats

        $this->totalRevenue = $allTransactions->sum('amount');
        $this->avgRevenue = $allTransactions->avg('amount');
        $last = $allTransactions->sortByDesc('id')->first();
        $this->lastPaymentDate = optional($last)->created_at?->format('d-M-Y h:iA');
        $this->lastPaymentAmount = optional($last)->amount;

        $grouped = $allTransactions->groupBy('category_id')->map(fn ($g) => $g->sum('amount'));
        // Map category IDs to names:
        $categoryNames = \App\Models\Category::whereIn('id', $grouped->keys())->pluck('name', 'id');

        $this->categoryRevenue = $grouped->mapWithKeys(function ($amount, $categoryId) use ($categoryNames) {
            return [$categoryNames[$categoryId] ?? 'Unknown' => $amount];
        });

        // $this->categoryRevenue = $allTransactions->groupBy('category_id')->map(fn ($g) => $g->sum('amount'));
        $this->revenueOverTime = $allTransactions->groupBy(fn ($item) =>
            Carbon::parse($item->created_at)->format('Y-m-d')
        )->map(fn ($g) => $g->sum('amount'));

        $this->dispatch('updatedchart',
            categoryLabels: $this->categoryRevenue->keys()->values(),
            categoryData: $this->categoryRevenue->values()->values(),
            revenueLabels: $this->revenueOverTime->keys()->values(),
            revenueData: $this->revenueOverTime->values()->values(),
        );

        return view('livewire.payment-report', [
            'transactions' => $paginated,
            'allTransactions' => $allTransactions,
        ]);
    }

    public function exportCSV()
    {
        $query = StripeResponse::query()
            ->where('team_id', $this->team_id)
            ->where('location_id', $this->location_id)
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->where('status', 'succeeded')
            ->latest();

        $transactions = $query->get();

        // Prepare CSV headers
        $csvData = [];
        $csvData[] = [
            __('report.Txn ID'),
            __('report.Token'),
            __('report.name'),
            __('report.contact'),
            __('report.Date'),
            __('report.Amount'),
            __('report.Currency'),
            __('report.Service'),
        ];

        // Add transaction data
        foreach ($transactions as $txn) {
            $csvData[] = [
                $txn->payment_intent_id ?? '',
                ($txn->queue?->start_acronym ?? '') . ' ' . ($txn->queue?->token ?? 'Booked'),
                $txn->queue?->queueStorages->first()?->name ?? '',
                $txn->queue?->queueStorages->first()?->phone ?? '',
                Carbon::parse($txn->created_at)->format('Y-m-d'),
                $txn->amount ?? '',
                strtoupper($txn->currency ?? ''),
                ucfirst($txn->category?->name ?? ''),
            ];
        }

        // Generate filename with date range
        $filename = 'payment_report_' . $this->startDate . '_to_' . $this->endDate . '_' . now()->format('Ymd_His') . '.csv';

        // Create CSV content
        $handle = fopen('php://temp', 'r+');
        foreach ($csvData as $line) {
            fputcsv($handle, $line);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return Response::streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $filename);
    }
}