<?php

namespace App\Livewire\Voucher;

use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class VoucherList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
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

    public function render()
    {
        $teamId = tenant('id') ?? (Auth::user()->team_id ?? null);
        
        $vouchers = Voucher::where('team_id', $teamId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('voucher_name', 'like', '%' . $this->search . '%')
                      ->orWhere('voucher_code', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.voucher.voucher-list', compact('vouchers'));
    }
}
