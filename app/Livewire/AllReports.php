<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AllReports extends Component
{
    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Reports')) {
            abort(403);
        }
    }

    public function render()
    {
        return view('livewire.all-reports');
    }
}
