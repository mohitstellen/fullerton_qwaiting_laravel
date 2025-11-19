<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MainBranchExport implements WithMultipleSheets
{
    use Exportable;

    protected $records;
    protected $domain;
    protected $filters;
    
    public function __construct($records,$filters,$domain)
    {
        $this->records = $records;
        $this->domain = $domain;
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        $sheets = [
           'Sheet 1'=> new BranchExport($this->records,$this->domain),
           'Sheet 2'=>new BranchSettingExport($this->filters),


        ];
        return $sheets;
    }
}
