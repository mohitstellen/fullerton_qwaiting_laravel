<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

class Integrations extends Component
{

    #[Title('Integrations')]

    public $integrations;

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Integration')) {
            abort(403);
        }
    }

    public function render()
    {
        return view('livewire.integrations');
    }
}
