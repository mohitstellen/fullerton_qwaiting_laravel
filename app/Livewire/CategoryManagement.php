<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Models\Category;
use App\Models\SiteDetail;
use Livewire\Attributes\On;
use App\Models\Location;
use App\Models\User;
use App\Models\CustomSlot;
use App\Models\AccountSetting;
use App\Models\Level;
use App\Models\ActivityLog;
use DB;
use Auth;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CategoryManagement extends Component
{
    use WithPagination;
    #[Title('Appointment Types')]

    public $locationId, $teamId, $tab = '1';
    // public $categories = [];
    public $search = '';
    public $corporateSearch = '';
    public $appointmentTypeFilter = ''; // For filtering packages by appointment type
    public $selectedCategory = null;
    public $selectedMultiple = [];
    public $selectAll = false;
    public $level1;
    public $level2;
    public $level3;
    public $userAuth;
    public $perPage = 25; // Number of records per page


    public function mount(Request $request)
    {
        $this->userAuth = Auth::user();
        if (!$this->userAuth->hasPermissionTo('Service Read')) {
            abort(403);
        }

        $this->teamId = tenant('id'); // Get the current tenant ID
        $this->locationId = Session::get('selectedLocation');
        $levels =  Level::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->whereIn('level', [1, 2, 3])
            ->get()
            ->keyBy('level');

        $this->level1 = 'Appointment Types';
        $this->level2 = 'Packages';
        // $this->level3 = $levels[3]->name ?? 'Level 3';

        $this->tab = $request->query('tab') ?? 1;
    }


    // Trigger loadCategories when search is updated
    public function updatedSearch()
    {
        // $this->loadCategories();
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage(); // Reset pagination when searching
    }

    public function updatedCorporateSearch()
    {
        $this->resetPage();
    }

    public function updatingCorporateSearch()
    {
        $this->resetPage(); // Reset pagination when searching
    }

    public function updatedAppointmentTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingAppointmentTypeFilter()
    {
        $this->resetPage(); // Reset pagination when filtering
    }

    public function updatingPerPage()
    {
        $this->resetPage(); // Reset pagination when per page changes
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
        $this->resetPage(); // Reload categories when tab changes
    }


    public function deleteCategory($id)
    {
        $this->selectedCategory = $id;
        $this->dispatch('confirm-delete');
    }

    #[On('confirmed-delete')]
    public function confirmDelete()
    {
        if ($this->selectedCategory) {
            DB::table('category_user')->where('category_id', $this->selectedCategory)->delete();
            AccountSetting::where('category_id', $this->selectedCategory)->delete();
            CustomSlot::where('category_id', $this->selectedCategory)->delete();

            Category::where('id', $this->selectedCategory)->delete(); // Delete the category
            $this->resetPage();
            // session()->flash('message', 'Staff deleted successfully.');
            ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, ActivityLog::DELETE, $this->locationId, ActivityLog::CATEGORY, null, $this->userAuth);
            $this->dispatch('deleted');
        }

        // $this->showDeleteConfirm = false;
    }

    #[On('bulkDelete')]
    public function updatedSelectAll($ids)
    {

        if ($ids) {
            $this->selectedMultiple = $ids;
        } else {
            $this->selectedMultiple = [];
        }
        $this->dispatch('confirm-multiple-delete');
    }

    #[On('confirmed-multiple-delete')]
    public function bulkDelete()
    {

        if (!empty($this->selectedMultiple)) {

            DB::table('category_user')->whereIn('category_id', $this->selectedMultiple)->delete();
            AccountSetting::whereIn('category_id', $this->selectedMultiple)->delete();
            CustomSlot::whereIn('category_id', $this->selectedMultiple)->delete();

            Category::whereIn('id', $this->selectedMultiple)->delete();
            $this->selectedMultiple = [];
            $this->selectAll = false;
            ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, ActivityLog::BULK_DELETE, $this->locationId, ActivityLog::CATEGORY, null, $this->userAuth);
            $this->dispatch('deleted');
        }
    }

    public function duplicateCategory($id)
    {
        // Redirect to create form with duplicate query parameter
        return redirect()->route('tenant.category.create', ['level' => $this->tab])->with('duplicate_category_id', $id);
    }

    public function exportCSV()
    {
        $query = Category::where('level_id', $this->tab)
            ->where('team_id', $this->teamId)
            ->whereJsonContains('category_locations', (string) $this->locationId)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->tab == 1 && $this->corporateSearch, function ($query) {
                $query->whereHas('company', function ($companyQuery) {
                    $companyQuery->where('company_name', 'like', '%' . $this->corporateSearch . '%');
                });
            })
            ->when($this->tab == 2 && $this->appointmentTypeFilter, function ($query) {
                $query->where('parent_id', $this->appointmentTypeFilter);
            });

        // Eager load relationships
        if ($this->tab == 1) {
            $query->with(['company', 'companyAppointmentTypes']);
        } elseif ($this->tab == 2) {
            $query->with('getparent');
        }

        $categories = $query->orderBy('name')->get();

        $csvData = [];

        if ($this->tab == 1) {
            // Appointment Types CSV
            $csvData[] = [
                'S.No',
                'Service Type Name',
                'Available Booking',
                'Amount',
                'Applicable for',
                'Corporate',
                'Status',
            ];

            foreach ($categories as $index => $category) {
                $csvData[] = [
                    $index + 1,
                    $category->name ?? '',
                    !empty($category->booking_category_show_for) ? 'Y' : 'N',
                    $category->amount ?? '0',
                    $category->companyAppointmentTypes->first()?->applicable_for ?? 'N/A',
                    $category->company?->company_name ?? '',
                    $category->deleted_at ? 'Inactive' : 'Active',
                ];
            }
            $filename = 'appointment_types_export_' . now()->format('Ymd_His') . '.csv';
        } else {
            // Packages CSV
            $csvData[] = [
                'S.No',
                'Package Name',
                'Appointment Type',
                'Amount',
            ];

            foreach ($categories as $index => $category) {
                $csvData[] = [
                    $index + 1,
                    $category->name ?? '',
                    $category->getparent?->name ?? 'N/A',
                    $category->amount ?? '0',
                ];
            }
            $filename = 'packages_export_' . now()->format('Ymd_His') . '.csv';
        }

        $handle = fopen('php://temp', 'r+');

        // Add BOM for UTF-8 to ensure proper encoding in Excel
        fwrite($handle, "\xEF\xBB\xBF");

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

        $siteSetting = SiteDetail::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->select('category_slot_level', 'enable_time_slot')
            ->first();
        $query = Category::where('level_id', $this->tab)
            ->where('team_id', $this->teamId)
            ->whereJsonContains('category_locations', (string) $this->locationId)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->tab == 1 && $this->corporateSearch, function ($query) {
                $query->whereHas('company', function ($companyQuery) {
                    $companyQuery->where('company_name', 'like', '%' . $this->corporateSearch . '%');
                });
            })
            ->when($this->tab == 2 && $this->appointmentTypeFilter, function ($query) {
                $query->where('parent_id', $this->appointmentTypeFilter);
            });

        // Eager load relationships based on level
        if ($this->tab == 1) {
            // For appointment types, load company and company appointment types
            $query->with(['company', 'companyAppointmentTypes']);
        } elseif ($this->tab == 2) {
            $query->with('getparent');
        } elseif ($this->tab == 3) {
            $query->with(['getparent.getparent']);
        }

        $categories = $query->paginate($this->perPage);

        // Get appointment types for filter (only when on packages tab)
        // Show ALL appointment types regardless of location
        $appointmentTypes = [];
        if ($this->tab == 2) {
            $level1 = Level::where('team_id', $this->teamId)
                ->where('location_id', $this->locationId)
                ->where('level', 1)
                ->first();

            if ($level1) {
                $appointmentTypes = Category::where('level_id', 1)
                    ->where('team_id', $this->teamId)
                    ->whereNull('deleted_at')
                    ->orderBy('name')
                    ->get();
            }
        }

        return view('livewire.category-management', [
            'categories' => $categories,
            'siteSetting' => $siteSetting,
            'appointmentTypes' => $appointmentTypes,
        ]);
    }
}
