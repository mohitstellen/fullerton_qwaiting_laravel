<?php

namespace App\Livewire\Voucher;

use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

use App\Models\Category;

class AddVoucher extends Component
{
    public array $voucher = [
        'voucher_name' => '',
        'category_id' => '',
        'voucher_code' => '',
        'valid_from' => '',
        'valid_to' => '',
        'discount_percentage' => '',
        'no_of_redemption' => '',
    ];

    protected array $messages = [
        'voucher.voucher_name.required' => 'The voucher name field is required.',
        'voucher.category_id.required' => 'The appointment type field is required.',
        'voucher.voucher_code.required' => 'The voucher code field is required.',
        'voucher.voucher_code.unique' => 'The voucher code has already been taken.',
        'voucher.valid_from.required' => 'The valid from field is required.',
        'voucher.valid_to.required' => 'The valid to field is required.',
        'voucher.discount_percentage.required' => 'The discount percentage field is required.',
        'voucher.discount_percentage.numeric' => 'The discount percentage must be a number.',
        'voucher.discount_percentage.min' => 'The discount percentage must be at least 0.',
        'voucher.discount_percentage.max' => 'The discount percentage must not exceed 100.',
    ];

    protected function rules(): array
    {
        return [
            'voucher.voucher_name' => ['required', 'string', 'max:255'],
            'voucher.category_id' => ['nullable', 'exists:categories,id'],
            'voucher.voucher_code' => ['required', 'string', 'max:255', 'unique:vouchers,voucher_code'],
            'voucher.valid_from' => ['required', 'date'],
            'voucher.valid_to' => ['required', 'date', 'after_or_equal:voucher.valid_from'],
            'voucher.discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'voucher.no_of_redemption' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $teamId = tenant('id') ?? (Auth::user()->team_id ?? null);

        Voucher::create([
            'team_id' => $teamId,
            'voucher_name' => $this->voucher['voucher_name'],
            'category_id' => $this->voucher['category_id'] ?: null,
            'voucher_code' => $this->voucher['voucher_code'],
            'valid_from' => $this->voucher['valid_from'],
            'valid_to' => $this->voucher['valid_to'],
            'discount_percentage' => $this->voucher['discount_percentage'],
            'no_of_redemption' => $this->voucher['no_of_redemption'] === '' ? null : $this->voucher['no_of_redemption'],
        ]);

        session()->flash('message', 'Voucher created successfully.');

        $this->redirectRoute('tenant.vouchers.index');
    }

    public function render()
    {
        $teamId = tenant('id') ?? (Auth::user()->team_id ?? null);
        $categories = Category::where('team_id', $teamId)->where('level_id', 1)->get();
        return view('livewire.voucher.add-voucher', compact('categories'));
    }
}
