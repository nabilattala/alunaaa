<?php

namespace App\Http\Controllers;

use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class   OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api'); // Pastikan middleware otentikasi digunakan

        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }


    public function index(Request $request)
    {
        try {
            $order = Order::query()->with(['user', 'product']);

            // Admin bisa melihat semua order
            if (auth()->user()->role === 'admin') {
                // Tidak perlu filter user_id
            }
            // Kelas hanya bisa melihat order untuk produk yang mereka jual
            elseif (auth()->user()->role === 'kelas') {
                $order->whereHas('product', function ($query) {
                    $query->where('user_id', auth()->id()); // Asumsi produk memiliki user_id yang merujuk ke kelas
                });
            }
            // Pengguna hanya bisa melihat order miliknya sendiri
            elseif (auth()->user()->role === 'pengguna') {
                $order->where('user_id', auth()->id());
            }

            // Filter by status
            if ($request->has('status')) {
                $order->where('status', $request->status);
            }

            // Filter by product_id
            if ($request->has('product_id')) {
                $order->where('product_id', $request->product_id);
            }

            // Filter by buyer (user_id)
            if ($request->has('buyer_id')) {
                $order->where('user_id', $request->buyer_id);
            }

            // Filter by date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $order->whereBetween('created_at', [
                    $request->start_date,
                    $request->end_date
                ]);
            }

            // Search by keyword
            if ($request->has('search')) {
                $keyword = $request->search;
                $order->where(function ($query) use ($keyword) {
                    $query->where('order_id', 'like', '%' . $keyword . '%') // Search by order_id
                        ->orWhereHas('product', function ($q) use ($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%'); // Search by product name
                        })
                        ->orWhereHas('user', function ($q) use ($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%'); // Search by buyer name
                        });
                });
            }

            // Sorting (default: latest first)
            $sortField = $request->has('sort_field') ? $request->sort_field : 'created_at';
            $sortOrder = $request->has('sort_order') ? $request->sort_order : 'desc';
            $order->orderBy($sortField, $sortOrder);

            // Pagination
            $orders = $order->paginate($request->per_page ?? 10);

            return response()->json([
                'status' => 'success',
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            Log::error('Order index error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch orders'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $product = Product::findOrFail($request->product_id);

            // Generate unique order ID
            $orderId = 'ORD-' . Str::random(5) . '-' . time();

            // Buat order
            $order = Order::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'order_id' => $orderId,
                'total_price' => $product->price,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_url' => null // Awalnya null
            ]);

            // Generate payment URL menggunakan Midtrans Snap
            $params = [
                'transaction_details' => [
                    'order_id' => $order->order_id, // ID order unik
                    'gross_amount' => $order->total_price, // Total harga
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name, // Nama pelanggan
                    'email' => auth()->user()->email, // Email pelanggan
                ],
            ];

            $snapToken = Snap::getSnapToken($params); // Generate Snap Token
            $paymentUrl = Snap::getSnapUrl($params); // Dapatkan payment URL

            // Simpan payment URL ke database
            $order->update([
                'payment_url' => $paymentUrl
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'order' => $order,
                    'payment_url' => $paymentUrl
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create order'
            ], 500);
        }
    }

    public function show(Order $order)
    {
        try {
            // Admin bisa melihat semua order
            if (auth()->user()->role === 'admin') {
                // Tidak perlu pengecekan tambahan
            }
            // Kelas hanya bisa melihat order untuk produk yang mereka jual
            elseif (auth()->user()->role === 'kelas') {
                if ($order->product->user_id !== auth()->id()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Unauthorized'
                    ], 403);
                }
            }
            // Pengguna hanya bisa melihat order miliknya sendiri
            elseif (auth()->user()->role === 'pengguna') {
                if ($order->user_id !== auth()->id()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Unauthorized'
                    ], 403);
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $order->load(['user', 'product'])
            ]);
        } catch (\Exception $e) {
            Log::error('Order show error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch order details'
            ], 500);
        }
    }

    public function updateStatus(Request $request, $order_id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,processing,completed,cancelled',
                'payment_status' => 'required|in:paid,unpaid,expired'
            ]);

            $order = Order::where('order_id', $order_id)->firstOrFail();

            $order->update([
                'status' => $request->status,
                'payment_status' => $request->payment_status,
                'midtrans_response' => $request->midtrans_response ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Order status updated successfully',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            Log::error('Order status update error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update order status'
            ], 500);
        }
    }

    public function destroy(Order $order)
    {
        try {
            $order->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Order deletion error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete order'
            ], 500);
        }
    }

    public function pay(Order $order)
    {
        // Pastikan order milik pengguna yang login
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        // Arahkan pengguna ke payment URL
        return redirect()->away($order->payment_url);
    }
}
