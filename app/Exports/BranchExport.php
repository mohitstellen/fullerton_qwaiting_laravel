<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\ {
 Queue,Tenant}
    ;
    use Filament\Facades\Filament;
    class BranchExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
 {
        /**
        * @return \Illuminate\Support\Collection
        */

        protected $records;
        protected $domain;

        public function __construct( $records,$domain)
 {
            $this->records = $records;
            $this->domain = $domain;
            
        }

        public function title(): string
 {
            return 'Sheet 1';
        }

        public function array(): array
 {

            $data = [];
            $data []  = [ __('report.Branch Report'), null, null, null,  Carbon::now()->format( 'F j, Y h:i A' ) ];

            $data[] =  [
          
                __('report.name'),
                __('report.Question'),
                __('report.Rating'),

            ];

            foreach ( $this->records as $branch ) {

                $rating = $branch->rating;
                $emojiText = Queue::getEmojiText();
                $emoji =  $emojiText[ $rating ][ 'emoji' ] ?? 'N/A';
                $teamName =  Tenant::memberDetail();
                $data[] = [
                   
                    $branch->user?->name ?? $branch->queues?->name,
                    $branch->question,
                    $rating,
                ];
            }

            return $data;
        }

        public function registerEvents(): array
 {
            return [
                AfterSheet::class => function ( AfterSheet $event ) {
                    $sheet = $event->sheet->getDelegate();

                    // Set specific column widths
                    $columnWidths = [
                        'A' => 25,
                        'B' => 25,
                        'C' => 25,
                        'D' => 25,
                        'E' => 25,
                        'F' => 25,
                        'G' => 25,
                     
                    ];
                    foreach ( $columnWidths as $column => $width ) {
                        $sheet->getColumnDimension( $column )->setWidth( $width );
                    }
                    $sheet->getRowDimension( 1 )->setRowHeight( 30 );

                    $sheet->mergeCells( 'A1:D1' );
                    $sheet->getStyle( 'A1' )->applyFromArray( [
                        'font' => [
                            'bold' => true,
                            'size' => 16,
                            'color' => [ 'argb' => '4F46E5' ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ] );
                    $sheet->mergeCells( 'E1:F1' );
                    $sheet->getStyle( 'J1' )->applyFromArray( [
                        'font' => [
                            'bold' => false,
                            'size' => 12,
                            'color' => [ 'argb' => '4A4B49' ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ] );

                    $sheet->getStyle( 'A2:F2' )->applyFromArray( [
                        'font' => [
                            'bold' => true,
                            'color' => [ 'argb' => 'FFFFFF' ],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => [ 'argb' => '4F46E5' ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'ertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ] );

                    $highestRow = $sheet->getHighestDataRow();

                    // Styling the rest of the rows with alternating colors starting from the first record row
                    for ( $row = 3; $row <= $highestRow; $row++ ) {

                        if ( $row % 2 == 0 ) {
                            $sheet->getStyle( 'A'. $row. ':F'. $row )->applyFromArray( [
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'color' => [ 'argb' => 'D3D3D3' ], // Light Grey background
                                ],
                            ] );
                        }
                    }
                }
                ,
            ];
        }

    }
