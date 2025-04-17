<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Total User
        $totalUsers = User::count();

        // Total Transaksi Berhasil (asumsi status 'success' di tabel orders)
        $totalSuccessfulTransactions = Order::where('status', 'success')->count();

        // Penjualan Per Minggu (7 hari ke belakang)
        $weeklySales = Order::where('status', 'success')
            ->whereBetween('created_at', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        return response()->json([
            'total_users' => $totalUsers,
            'total_successful_transactions' => $totalSuccessfulTransactions,
            'weekly_sales' => $weeklySales
        ]);
    }
}
