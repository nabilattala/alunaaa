<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Product::with(['category', 'user', 'discounts', 'ratings'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Judul Produk',
            'Deskripsi',
            'Harga',
            'Harga Final',
            'Kategori',
            'Pembuat',
            'Status',
            'Rating Rata-rata',
            'URL',
            'URL Video',
            'Kode Diskon',
            'Tanggal Dibuat',
            'Tanggal Diupdate'
        ];
    }

    public function map($product): array
    {
        $discountCodes = $product->discounts->map(function ($discount) {
            return $discount->code . ' (' . $discount->percentage . '%)';
        })->implode(', ');

        $totalDiscount = $product->discounts->sum('percentage');
        $finalPrice = $product->price - ($product->price * ($totalDiscount / 100));

        return [
            $product->id,
            $product->title,
            $product->description,
            $product->price,
            $finalPrice,
            optional($product->category)->name ?? 'Tidak ada',
            optional($product->user)->username ?? 'Tidak ada',
            $product->status,
            $product->ratings->avg('rating') ? round($product->ratings->avg('rating'), 1) : 'Belum ada rating',
            $product->url,
            $product->video_url,
            $discountCodes ?: 'Tidak ada diskon',
            $product->created_at ? $product->created_at->format('d-m-Y H:i') : '-',
            $product->updated_at ? $product->updated_at->format('d-m-Y H:i') : '-',
        ];
    }

}