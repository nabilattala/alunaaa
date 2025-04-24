@component('mail::message')
# Terima Kasih, {{ $order->user->username }}!

Berikut adalah invoice untuk pesanan Anda:

**Invoice Number:** {{ $order->invoice_number }}

@component('mail::table')
| Produk                  | Jumlah | Harga               |
|:------------------------|:--------:|:--------------------|
@foreach ($order->orderItems as $item)
| {{ $item->product->title }} | {{ $item->quantity }} | Rp{{ number_format($item->price, 0, ',', '.') }} |
@endforeach
@endcomponent

**Total:** **Rp{{ number_format($order->total_price, 0, ',', '.') }}**

Terima kasih sudah belanja di **{{ config('app.name') }}**!
@endcomponent
