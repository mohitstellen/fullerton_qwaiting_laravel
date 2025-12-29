<?php

namespace App\Livewire\Voucher;

use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class VoucherList extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 25; // Number of records per page

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $voucherId): void
    {
        $this->dispatch('confirm-voucher-delete', voucherId: $voucherId);
    }

    #[On('delete-voucher-confirmed')]
    public function deleteVoucher(int $voucherId): void
    {
        Voucher::whereKey($voucherId)->delete();
        session()->flash('message', 'Voucher deleted successfully.');
    }

    private function vouchersQuery()
    {
        $teamId = tenant('id') ?? (Auth::user()->team_id ?? null);
        
        return Voucher::where('team_id', $teamId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('voucher_name', 'like', '%' . $this->search . '%')
                      ->orWhere('voucher_code', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc');
    }

    public function exportCSV()
    {
        $vouchers = $this->vouchersQuery()->get();

        $csvData = [];
        $csvData[] = [
            'S.No',
            'Voucher Name',
            'Voucher Code',
            'Valid From',
            'Valid To',
            'Discount (%)',
            'No. of Redemption',
            'Created At',
        ];

        foreach ($vouchers as $index => $voucher) {
            $csvData[] = [
                $index + 1,
                $voucher->voucher_name,
                $voucher->voucher_code,
                $voucher->valid_from->format('d-m-Y'),
                $voucher->valid_to->format('d-m-Y'),
                $voucher->discount_percentage,
                $voucher->no_of_redemption ?? '-',
                $voucher->created_at->format('Y-m-d H:i:s'),
            ];
        }

        $filename = 'vouchers_export_' . now()->format('Ymd_His') . '.csv';
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF"); // Add BOM for UTF-8
        foreach ($csvData as $line) {
            fputcsv($handle, $line);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return Response::streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function render()
    {
        $vouchers = $this->vouchersQuery()->paginate($this->perPage);

        return view('livewire.voucher.voucher-list', compact('vouchers'));
    }
}
