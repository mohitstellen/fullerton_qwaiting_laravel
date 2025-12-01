<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Models\Category;
use App\Models\PaymentSetting;
use App\Models\Level;
use App\Models\Location;
use App\Models\FormField;
use App\Models\Company;
use App\Models\NotificationTemplate;
use Livewire\WithFileUploads;
use Auth;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;

class CategoryCreateComponent extends Component
{

    use WithFileUploads;

    #[Title('Appointment Type')]

    public $locationId, $teamId, $tab = '1';
    public $name, $other_name, $acronym, $visitor_in_queue, $locations, $img, $sort, $amount, $is_paid;
    public $display_on = "Display on Transfer & Ticket Screen";
    public $for_screen = "Display on Walk-In & Appointment Screen";
    public $booking_category_show_for = "Backend & Online Appointment Screen";
    public $isEdit = false;
    public $allLocations = [];
    public $category = [];
    public $parent_id = null;
    public $paymentSet;
    public $parentCategory = [];
    public $redirectUrl;
    public $isService = false;
    public $serviceTime;
    public $note;
    public $description;
    public $ticket_note;
    public $label_image;
    public $service_color;
    public $label_background_color;
    public $label_font_color;
    public $label_text;
    public $bg_color;
    public $leadTimeValue;
    public $leadTimeUnit = 'days';
    public $enableEVoucher = '0';
    public $company_id = null;
    public $companySearch = '';
    public $allCompanies = [];
    public $selectedCompanyName = '';
    public $showCompanyDropdown = false;

    // Email templates
    public $confirmationTitle = '';
    public $confirmationContent = '';
    public $reschedulingTitle = '';
    public $reschedulingContent = '';
    public $cancelTitle = '';
    public $cancelContent = '';

    public function mount($level = null, $categoryId = null)
    {

        $user = Auth::user();
        if (!$user->hasAnyPermission(['Service Add', 'Service Edit'])) {
            abort(403);
        }

        if ($level == null) {
            return redirect()->back();
        }

        $this->teamId = tenant('id'); // Get the current tenant ID
        $this->locationId = Session::get('selectedLocation');

        $levels =  Level::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            // ->where('level', $level)
            ->get();

        $this->paymentSet = PaymentSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->select('id', 'enable_payment', 'category_level')
            ->first();

        $this->tab = $level;
        $this->visitor_in_queue = 1;
        $this->sort = 0;
        $this->leadTimeValue = null;
        $this->leadTimeUnit = 'days';
        $this->enableEVoucher = '0';

        // Check if this is a duplicate request
        $duplicateCategoryId = Session::get('duplicate_category_id');
        if ($duplicateCategoryId) {
            Session::forget('duplicate_category_id'); // Clear the session after use
            $this->category = Category::findOrFail($duplicateCategoryId);
            $this->isEdit = false; // This is a new category, not an edit
            $this->loadCategoryData();
        } elseif ($categoryId) {
            $this->category = Category::findOrFail($categoryId);
            $this->isEdit = true;
            $this->loadCategoryData();
        }

        if ($level == 2) {

            $this->parentCategory = Category::where('team_id', $this->teamId)
                ->whereJsonContains('category_locations', "$this->locationId")
                ->where('level_id', 1)
                ->where(function ($query) {
                    $query->whereNull('parent_id')
                        ->orWhere('parent_id', '');
                })
                ->select('id', 'name')
                ->get();
        } elseif ($level == 3) {
            $this->parentCategory = Category::where('team_id', $this->teamId)
                ->whereJsonContains('category_locations', "$this->locationId")
                ->where('level_id', 2)
                ->whereNotNull('parent_id')
                ->select('id', 'name')
                ->get();
        }

        if (Auth::user()->is_admin == 1) {
            $this->allLocations = Location::where('team_id', tenant('id'))
                ->where('status', 1)
                ->select('location_name', 'id')
                ->get();
        } else {
            $this->allLocations = Location::where('team_id', tenant('id'))
                ->where('id', Auth::user()?->locations)
                ->where('status', 1)
                ->select('location_name', 'id')
                ->get();
        }

        // Don't load all companies initially - only load when searching
        $this->allCompanies = [];
    }

    public function updatedCompanySearch()
    {
        if (strlen($this->companySearch) >= 1) {
            $this->allCompanies = Company::where('team_id', $this->teamId)
                ->where('status', 'active')
                ->where('company_name', 'like', '%' . $this->companySearch . '%')
                ->select('id', 'company_name')
                ->orderBy('company_name')
                ->limit(20)
                ->get();
            $this->showCompanyDropdown = true;
        } elseif (empty($this->companySearch)) {
            $this->allCompanies = [];
            $this->showCompanyDropdown = false;
            if ($this->company_id) {
                // Load selected company name
                $company = Company::find($this->company_id);
                $this->selectedCompanyName = $company ? $company->company_name : '';
            }
        }
    }

    public function selectCompany($companyId, $companyName)
    {
        $this->company_id = $companyId;
        $this->selectedCompanyName = $companyName;
        $this->companySearch = $companyName;
        $this->showCompanyDropdown = false;
    }

    public function clearCompany()
    {
        $this->company_id = null;
        $this->selectedCompanyName = '';
        $this->companySearch = '';
        $this->showCompanyDropdown = false;
        $this->allCompanies = [];
    }

    private function loadCategoryData()
    {
        $this->name = $this->category->name;
        $this->other_name = $this->category->other_name;
        $this->acronym = $this->category->acronym;
        $this->display_on = $this->category->display_on;
        $this->sort = $this->category->sort ?? 0;
        $this->for_screen = $this->category->for_screen;
        $this->booking_category_show_for = $this->category->booking_category_show_for;
        $this->visitor_in_queue = $this->category->visitor_in_queue ?? 1;
        $this->locations = $this->category->category_locations;
        $this->parent_id = $this->tab > 1 ? $this->category->parent_id : '';
        $this->is_paid = $this->category->is_paid ?? 0;
        $this->amount = $this->category->amount ?? 0;
        $this->redirectUrl = $this->category->redirect_url ?? '';
        $this->isService = (bool)$this->category->is_service_template;
        $this->serviceTime = $this->category->service_time ?? '';
        $this->leadTimeValue = $this->category->lead_time_value;
        $this->leadTimeUnit = $this->category->lead_time_unit ?? 'days';
        $this->enableEVoucher = $this->category->enable_e_voucher ? '1' : '0';
        $this->note = $this->category->note ?? '';
        $this->description = $this->category->description ?? '';
        $this->ticket_note = $this->category->ticket_note ?? '';
        $this->service_color = $this->category->service_color ?? '#fff';
        $this->label_background_color = $this->category->label_background_color ?? '#ffffff';
        $this->label_font_color = $this->category->label_font_color ?? '#000000';
        $this->label_text = $this->category->label_text ?? '';
        $this->bg_color = $this->category->bg_color ?? '';
        $this->company_id = $this->category->company_id ?? null;

        // Load selected company name if editing
        if ($this->company_id) {
            $company = Company::find($this->company_id);
            $this->selectedCompanyName = $company ? $company->company_name : '';
            $this->companySearch = $this->selectedCompanyName;
        }

        // Load email templates
        $this->loadEmailTemplates();
    }

    private function loadEmailTemplates()
    {
        if (!$this->isEdit || !$this->category) {
            return;
        }

        $template = NotificationTemplate::where('appointment_type_id', $this->category->id)
            ->where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->first();

        if ($template) {
            if ($template->appointment_confirmation_email) {
                $this->confirmationTitle = $template->appointment_confirmation_email['subject'] ?? '';
                $this->confirmationContent = $template->appointment_confirmation_email['body'] ?? '';
            }
            if ($template->appointment_rescheduling_email) {
                $this->reschedulingTitle = $template->appointment_rescheduling_email['subject'] ?? '';
                $this->reschedulingContent = $template->appointment_rescheduling_email['body'] ?? '';
            }
            if ($template->appointment_cancel_email) {
                $this->cancelTitle = $template->appointment_cancel_email['subject'] ?? '';
                $this->cancelContent = $template->appointment_cancel_email['body'] ?? '';
            }
        }
    }


    public function removeTempImage()
    {
        $this->img = null;
    }

    public function deleteOldImage()
    {
        if ($this->isEdit && $this->category->img && \Storage::disk('public')->exists($this->category->img)) {
            \Storage::disk('public')->delete($this->category->img);
        }

        $this->category->img = null;
        $this->category->save();

        // Also clear preview
        $this->img = null;
    }

    public function deleteOldLabelImage()
    {
        if ($this->isEdit && $this->category->label_image && \Storage::disk('public')->exists($this->category->label_image)) {
            \Storage::disk('public')->delete($this->category->label_image);
        }

        $this->category->label_image = null;
        $this->category->save();

        // Also clear preview
        $this->img = null;
    }


    public function saveCategory()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'other_name' => 'nullable|string|max:255',
            'acronym' => 'nullable|string',
            'display_on' => 'nullable|string',
            'for_screen' => 'nullable|string',
            'booking_category_show_for' => 'nullable|string',
            'visitor_in_queue' => 'nullable|integer',
            'sort' => 'nullable|integer',
            'ticket_note' => 'nullable|string|max:500',
            'note' => 'nullable|string|max:500',
            'serviceTime' => 'nullable|integer',
            'leadTimeValue' => $this->tab == 1 ? 'nullable|integer|min:0' : 'nullable',
            'leadTimeUnit' => $this->tab == 1 ? 'nullable|in:minutes,hours,days' : 'nullable',
            'enableEVoucher' => $this->tab == 2 ? 'nullable|in:0,1' : 'nullable',
            'label_text' => 'nullable|string|max:50',
            'locations' => 'required|array',
            'img' => $this->isEdit ? 'nullable|mimes:jpg,jpeg,png|max:2048' : 'nullable|mimes:jpg,jpeg,png|max:2048',
            'label_image' => $this->isEdit ? 'nullable|mimes:jpg,jpeg,png|max:2048' : 'nullable|mimes:jpg,jpeg,png|max:2048',
            'parent_id' => $this->tab > 1 ? 'required|integer' : 'nullable',
            'company_id' => 'nullable|integer|exists:companies,id',
            'confirmationTitle' => 'required|string|max:255',
        ], [

            'parent_id.required' => 'Parent Service is required',
            'confirmationTitle.required' => 'Confirmation email subject is required',
        ]);

        if ($this->paymentSet && $this->paymentSet->enable_payment == 1 && $this->paymentSet->category_level == $this->tab) {
            $this->validate([
                'is_paid' => 'required|in:0,1',
            ]);

            if ($this->is_paid == 1) {
                $this->validate([
                    'amount' => 'required|numeric|min:0',
                ]);
            }
        }


        // Unique check for each location
        foreach ($this->locations as $locationId) {
            $exists = Category::where('name', $this->name)
                ->where('team_id', $this->teamId)
                ->when(!empty($this->parent_id), fn($q) => $q->where('parent_id', $this->parent_id))
                ->whereJsonContains('category_locations', "$locationId")
                ->when($this->isEdit, fn($q) => $q->where('id', '!=', $this->category->id))
                ->exists();

            if ($exists) {
                $this->addError('name', 'The category name already exists for one of the selected locations.');
                return;
            }
        }

        // image handling
        $imagePath = $this->isEdit ? $this->category->img : null;
        $labelImagePath = $this->isEdit ? $this->category->label_image : null;


        if ($this->img) {
            // Store new image
            $imagePath = $this->img->store('category', 'public');
        }

        if ($this->label_image) {
            // Store new image
            $labelImagePath = $this->label_image->store('category', 'public');
        }



        // Prepare data
        $data = [
            'team_id' => $this->teamId,
            'level_id' => $this->tab,
            'name' => $this->name,
            'parent_id' => $this->parent_id ?? null,
            'other_name' => $this->other_name,
            'acronym' => $this->acronym,
            'display_on' => $this->display_on,
            'sort' =>  empty($this->sort) ? 0 : $this->sort,
            'for_screen' => $this->for_screen,
            'booking_category_show_for' => $this->booking_category_show_for,
            'visitor_in_queue' => !empty($this->visitor_in_queue) ? (int) $this->visitor_in_queue : 1,
            'category_locations' => $this->locations,
            'img' => $imagePath,
            'redirect_url' => $this->redirectUrl,
            'is_service_template' => $this->isService ? 1 : 0,
            'service_time' => $this->serviceTime ?? '',
            'lead_time_value' => $this->tab == 1 && $this->leadTimeValue !== '' ? (int) $this->leadTimeValue : null,
            'lead_time_unit' => $this->tab == 1 && $this->leadTimeValue !== '' ? $this->leadTimeUnit : null,
            'enable_e_voucher' => $this->tab == 2 ? (bool) ((int) $this->enableEVoucher) : 0,
            'note' => $this->note ?? '',
            'description' => $this->description ?? '',
            'ticket_note' => $this->ticket_note ?? '',
            'label_image' => $labelImagePath,
            'service_color' => $this->service_color ?? '#fff',
            'label_background_color' => $this->label_background_color ?? '#ffffff',
            'label_font_color' => $this->label_font_color ?? '#000000',
            'label_text' => $this->label_text ?? '',
            'bg_color' => $this->bg_color ?? '',
            'company_id' => $this->company_id ?? null,
        ];

        // Add paid details if applicable
        if ($this->paymentSet && $this->paymentSet->enable_payment == 1) {
            $data['is_paid'] = $this->is_paid ?? 0;
            $data['amount'] = $this->amount ?? 0;
        } else {
            $data['is_paid'] = 0;
            $data['amount'] = 0;
        }

        // Save or update
        $categoryDetail = Category::updateOrCreate(
            ['id' => $this->isEdit ? $this->category->id : null],
            $data
        );

        if ($this->isEdit) {

            $this->dispatch('updated',  '/category-management?tab=' . $this->tab);
        } else {
            // If this is a duplicate, copy relationships from original category
            // Check if $this->category is actually a Category instance (not just an array)
            if ($this->category instanceof \App\Models\Category && !$this->isEdit) {
                // Copy form_fields relationship
                $formFieldIds = $this->category->form_fields()->pluck('id')->toArray();
                if (!empty($formFieldIds)) {
                    $categoryDetail->form_fields()->sync($formFieldIds);
                }

                // Copy users relationship (category_user table)
                $userIds = DB::table('category_user')
                    ->where('category_id', $this->category->id)
                    ->pluck('user_id')
                    ->toArray();

                if (!empty($userIds)) {
                    $syncData = [];
                    foreach ($userIds as $userId) {
                        $syncData[$userId] = [];
                    }
                    $categoryDetail->users()->sync($syncData);
                }

                // Copy screen_template relationship
                $screenTemplateIds = $this->category->screenTemplate()->pluck('id')->toArray();
                if (!empty($screenTemplateIds)) {
                    $categoryDetail->screenTemplate()->sync($screenTemplateIds);
                }
            } else {
                // Original behavior for new categories
                $formfield = FormField::where('team_id', $this->teamId)->where('location_id', $this->locationId)->first();

                if (!empty($formfield)) {
                    $formfield->categories()->sync($categoryDetail);
                }
            }

            $this->dispatch('created', '/category-management?tab=' . $this->tab);
        }

        // Save email templates
        $this->saveEmailTemplates($categoryDetail->id);
    }

    private function saveEmailTemplates($categoryId)
    {
        $emailTemplates = [
            'appointment_confirmation_email' => [
                'subject' => $this->confirmationTitle ?? '',
                'body' => $this->confirmationContent ?? '',
            ],
            'appointment_rescheduling_email' => [
                'subject' => $this->reschedulingTitle ?? '',
                'body' => $this->reschedulingContent ?? '',
            ],
            'appointment_cancel_email' => [
                'subject' => $this->cancelTitle ?? '',
                'body' => $this->cancelContent ?? '',
            ],
        ];

        NotificationTemplate::updateOrCreate(
            [
                'appointment_type_id' => $categoryId,
                'team_id' => $this->teamId,
                'location_id' => $this->locationId,
            ],
            [
                'appointment_confirmation_email' => $emailTemplates['appointment_confirmation_email'],
                'appointment_rescheduling_email' => $emailTemplates['appointment_rescheduling_email'],
                'appointment_cancel_email' => $emailTemplates['appointment_cancel_email'],
            ]
        );
    }

    public function render()
    {
        return view('livewire.category-create-component');
    }
}
