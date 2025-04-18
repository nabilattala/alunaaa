<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class OrderExport implements FromCollection, WithHeadings
{
    protected $status;
    protected $startDate;
    protected $endDate;

    public function __construct($status = null, $startDate = null, $endDate = null)
    {
        $this->status = $status;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = Order::with('user', 'product');

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', Carbon::parse($this->startDate));
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', Carbon::parse($this->endDate));
        }

        return $query->get()->map(function ($order) {
            return [
                'Order ID' => $order->order_id,
                'User Name' => $order->user->name,
                'Product' => $order->product->name,
                'Total Price' => $order->total_price,
                'Status' => $order->status,
                'Payment Status' => $order->payment_status,
                'Created At' => $order->created_at->format('Y-m-d H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'User Name',
            'Product',
            'Total Price',
            'Status',
            'Payment Status',
            'Created At',
        ];
    }
}
