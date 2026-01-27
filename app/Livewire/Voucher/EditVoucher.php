<?php

namespace App\Livewire\Voucher;

use App\Models\Voucher;
use Illuminate\Validation\Rule;
use Livewire\Component;


use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class EditVoucher extends Component
{
    public Voucher $voucherModel;

    public array $voucher = [];

    public array $cardTypes = [];

    public string $selectedCardType = '';

    public array $cardTypeOptions = [
        '' => 'Please Choose',
        'MASTERCARD' => 'MASTERCARD',
        'VISA' => 'VISA',
        'AMEX' => 'AMEX',
        'DINERS' => 'DINERS',
        'Union Pay' => 'Union Pay',
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
            'voucher.voucher_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vouchers', 'voucher_code')->ignore($this->voucherModel->id),
            ],
            'voucher.valid_from' => ['required', 'date'],
            'voucher.valid_to' => ['required', 'date', 'after_or_equal:voucher.valid_from'],
            'voucher.discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'voucher.no_of_redemption' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function mount(Voucher $voucherRecord): void
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Voucher')) {
            abort(403);
        }
        
        $this->voucherModel = $voucherRecord;

        $this->voucher = [
            'voucher_name' => $voucherRecord->voucher_name,
            'category_id' => $voucherRecord->category_id,
            'voucher_code' => $voucherRecord->voucher_code,
            'valid_from' => $voucherRecord->valid_from ? $voucherRecord->valid_from->format('Y-m-d') : '',
            'valid_to' => $voucherRecord->valid_to ? $voucherRecord->valid_to->format('Y-m-d') : '',
            'discount_percentage' => $voucherRecord->discount_percentage,
            'no_of_redemption' => $voucherRecord->no_of_redemption,
        ];

        $this->loadCardTypes();
    }

    public function loadCardTypes(): void
    {
        $cardTypes = $this->voucherModel->card_types ?? [];
        $this->cardTypes = array_map(function ($cardType, $index) {
            return [
                'id' => $index,
                'card_type' => $cardType,
            ];
        }, $cardTypes, array_keys($cardTypes));
    }

    public function addCardType(): void
    {
        if (empty($this->selectedCardType)) {
            session()->flash('error', 'Please select a card type.');
            return;
        }

        $cardTypes = $this->voucherModel->card_types ?? [];

        // Check if card type already exists
        if (in_array($this->selectedCardType, $cardTypes)) {
            session()->flash('error', 'This card type has already been added.');
            return;
        }

        $cardTypes[] = $this->selectedCardType;
        $this->voucherModel->card_types = $cardTypes;
        $this->voucherModel->save();

        $this->selectedCardType = '';
        $this->loadCardTypes();
        session()->flash('message', 'Card type added successfully.');
    }

    public function removeCardType(int $index): void
    {
        $cardTypes = $this->voucherModel->card_types ?? [];
        if (isset($cardTypes[$index])) {
            unset($cardTypes[$index]);
            $this->voucherModel->card_types = array_values($cardTypes); // Re-index array
            $this->voucherModel->save();
            $this->loadCardTypes();
            session()->flash('message', 'Card type removed successfully.');
        }
    }

    public function update(): void
    {
        $this->validate();

        $this->voucherModel->update([
            'voucher_name' => $this->voucher['voucher_name'],
            'category_id' => $this->voucher['category_id'] ?: null,
            'voucher_code' => $this->voucher['voucher_code'],
            'valid_from' => $this->voucher['valid_from'],
            'valid_to' => $this->voucher['valid_to'],
            'discount_percentage' => $this->voucher['discount_percentage'],
            'no_of_redemption' => $this->voucher['no_of_redemption'] === '' ? null : $this->voucher['no_of_redemption'],
            // card_types is updated separately via addCardType/removeCardType methods
        ]);

        session()->flash('message', 'Voucher updated successfully.');

        $this->redirectRoute('tenant.vouchers.index');
    }

    public function render()
    {
        $teamId = tenant('id') ?? (Auth::user()->team_id ?? null);
        $categories = Category::where('team_id', $teamId)->get();
        return view('livewire.voucher.edit-voucher', compact('categories'));
    }
}
