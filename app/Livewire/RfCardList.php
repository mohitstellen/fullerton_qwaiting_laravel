<?php

namespace App\Livewire;

use App\Models\RfCard;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;

class RfCardList extends Component
{
    use WithPagination;

    #[Title('RF Cards')]
    public $teamId;
    public $location;
    public string $search = '';
    public int $perPage = 10;
    public $selectedId;

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');

    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[On('confirmed-rf-delete')]
    public function delete()
    {
        try {
            $rfCard = RfCard::findOrFail($this->selectedId);
            $rfCard->delete();
            $this->selectedId = null;
            
            $this->dispatch('rf-notify');
        } catch (\Exception $e) {
            $this->dispatch('rf-notify');
        }
    }

    public function confirmDelete($id)
    {
        $this->selectedId = $id;
        $this->dispatch('rf-confirm');
    }

    public function render()
    {
        $rfCards = RfCard::query()
            ->where('team_id', $this->teamId)
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('rfcard', 'like', $term)
                       ->orWhere('username', 'like', $term)
                       ->orWhere('name', 'like', $term)
                       ->orWhere('contact', 'like', $term)
                       ->orWhere('email', 'like', $term);
                });
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.rf-card-list', compact('rfCards'));
    }
}
