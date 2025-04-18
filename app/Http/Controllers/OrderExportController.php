<?php

namespace App\Http\Controllers;

use App\Exports\OrderExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class OrderExportController extends Controller
{
    public function export(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:pending,completed,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        return Excel::download(new OrderExport($status, $startDate, $endDate), 'orders.xlsx');
    }

}
