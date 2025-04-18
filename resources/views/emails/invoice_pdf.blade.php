<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $order->invoice_number }}</title>
</head>
<body>
    <h2>Invoice Order #{{ $order->invoice_number }}</h2>
    <p>Nama: {{ $order->user->name }}</p>
    <p>Email: {{ $order->user->email }}</p>
    <table border="1" cellpadding="5" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->orderItems as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>Rp{{ number_format($item->price) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <h3>Total: Rp{{ number_format($order->total_price) }}</h3>
</body>
</html>
