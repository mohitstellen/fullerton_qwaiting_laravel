<?php

namespace App\Livewire\Company;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CompanyForm extends Component
{
    public function render()
    {
        //test
        return view('livewire.company-form');
    }
}
