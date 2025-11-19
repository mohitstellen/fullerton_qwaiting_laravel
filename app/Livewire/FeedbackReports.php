<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Rating;
use App\Models\Location;
use App\Models\SiteDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Queue;
use Livewire\Attributes\Title;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MainBranchExport;
use Barryvdh\DomPDF\Facade\Pdf;

class FeedbackReports extends Component
{
    use WithPagination;

    #[Title('Feedback Report')]

    public $createdFrom;
    public $createdUntil;
    public $selectedLocation;
    public $teamId;
    public $name;
    public $domain;
    public $search;

    public function mount()
    {
        $this->selectedLocation = Session::get('selectedLocation');
        $this->createdFrom = now()->startOfMonth()->toDateString();
        $this->createdUntil = now()->toDateString();
        $this->teamId = tenant('id');
        $this->domain = tenant('name');
       
    }

    public function updating($field)
    {
        $this->resetPage();
    }

    public function getStats()
    {
        $query = Queue::where('team_id', $this->teamId)->where('locations_id', $this->selectedLocation);
        $ratingQuery = Rating::where('team_id', $this->teamId)->where('location_id',$this->selectedLocation);

        if ($this->createdFrom) {
            $query->whereDate('created_at', '>=', $this->createdFrom);
            $ratingQuery->whereDate('created_at', '>=', $this->createdFrom);
        }

        if ($this->createdUntil) {
            $query->whereDate('created_at', '<=', $this->createdUntil);
            $ratingQuery->whereDate('created_at', '<=', $this->createdUntil);
        }

        return [
            'totalQueue' => $query->count(),
            'closedQueue' => $query->where('status', 'Close')->count(),
            'averageRating' => number_format($ratingQuery->average('rating'), 2),
        ];
    }

    // public function exportcsv() {
       
    //     $summaryW = new FeedbackStatisticsChart();

    //     $summaryW->fromSelectedDate = $this->createdFrom;
    //     $summaryW->toSelectedDate =  $this->createdUntil;
    //     $summaryW->teamId = $this->teamId;
    //     // $summaryW->location = $this->location;
    //     $dataFromWidget = $summaryW->getData();

    //     $dataSetCallSummary = [
    //         'type' => 'bar',
    //         'data' => $dataFromWidget
    //     ];
    //     $imageExcel[ 'feedbackChart' ]  = SiteDetail::createImage( $dataSetCallSummary, 'feedback-report-chart-'.$this->teamId.'.png' );

    //     $imageExcel['start_date'] = Carbon::parse($this->createdFrom)->format( 'd-m-Y' );
    //     $imageExcel['end_date'] = Carbon::parse( $this->createdUntil)->format( 'd-m-Y' );
    //     $imageExcel['teamId'] = $this->teamId;
    //     $imageExcel['location'] = $this->selectedLocation;

    //     return Excel::download(new FeedbackStaticExport($imageExcel), ' statistics-report.xlsx');
    // }
    public function exportcsv() {
   
        $records = Rating::where('team_id', $this->teamId)
        ->where('location_id', $this->selectedLocation)
        ->when($this->createdFrom, fn ($query) => $query->whereDate('created_at', '>=', $this->createdFrom))
        ->when($this->createdUntil, fn ($query) => $query->whereDate('created_at', '<=', $this->createdUntil))->get();
        
        $domain=$this->domain;
        $filters = [];
        $currentDate = Carbon::now()->format( 'd-m-Y' );

        $filters[ 'Branch Name' ] = Location::locationName( $this->selectedLocation );
        $filters[ 'Created From' ]  = $this->createdFrom;
        $filters[ 'Created Until' ]  = $this->createdUntil;
       
        // if (isset($tables['ListMonthlyReports_filters'])) 
        //    $filters =  $tables['ListBranchReports_filters'];

        // $filters =  Rating::filterSettingExcel( $selectedLocation, $filters );
        return Excel::download( new MainBranchExport( $records, $filters,$domain ), 'branch-report.xlsx' );
    }

    public function exportpdf()
{
    $records = Rating::where('team_id', $this->teamId)
        ->where('location_id', $this->selectedLocation)
        ->when($this->createdFrom, fn ($query) => $query->whereDate('created_at', '>=', $this->createdFrom))
        ->when($this->createdUntil, fn ($query) => $query->whereDate('created_at', '<=', $this->createdUntil))
        ->get();
        $logo_src =  SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId,$this->selectedLocation);
    $filters = [
        'Branch Name' => Location::locationName($this->selectedLocation),
        'Created From' => $this->createdFrom,
        'Created Until' => $this->createdUntil
    ];

    $domain = $this->domain;

    $pdf = Pdf::loadView('pdf.feedback-pdf', compact('records', 'filters', 'domain','logo_src'));
    return response()->streamDownload(function () use ($pdf) {
        echo $pdf->stream();
    }, 'feedback-report.pdf');
}

    public function render()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Report Read')) {
            abort(403);
        }
        $cardsDetails= [];
        $cardsDetails =$this->getStats();

        $reports = Rating::where('team_id', $this->teamId)
            ->where('location_id', $this->selectedLocation)
            ->when($this->createdFrom, fn ($query) => $query->whereDate('created_at', '>=', $this->createdFrom))
            ->when($this->createdUntil, fn ($query) => $query->whereDate('created_at', '<=', $this->createdUntil))
            // ->when($this->search, function ($query) {
            //     $query->where(function ($q) {
            //         $q->where('name', 'like', '%' . $this->search . '%')
            //           ->orWhere('question', 'like', '%' . $this->search . '%')
            //           ->orWhere('comment', 'like', '%' . $this->search . '%');
            //     });
            // })
           
             ->paginate(10);

        return view('livewire.feedback-reports', compact('reports','cardsDetails'));
    }
}
