<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExportWithStyles extends ProductsExport implements WithStyles
{
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
            
            // Style the price column
            'D' => ['numberFormat' => ['formatCode' => '#,##0']],
            'E' => ['numberFormat' => ['formatCode' => '#,##0']],
            
            // Set auto size for all columns
            'A:Z' => ['autoSize' => true],
        ];
    }
}