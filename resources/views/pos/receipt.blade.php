<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt: {{ $order->order_number }}</title>
    <style>
        @page {
            margin: 0;
            size: 80mm auto;
        }

        body {
            font-family: monospace;
            font-size: 12px;
            line-height: 1.3;
            margin: 0;
            padding: 5px;
            max-width: 80mm;
            background-color: white;
        }

        .container {
            padding: 5px;
        }

        .logo-text {
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 2px;
        }

        .store-name {
            text-align: center;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .receipt-info {
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .receipt-info table {
            width: 100%;
        }

        .dashed-line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .order-table td {
            padding: 2px 0;
        }

        .right {
            text-align: right;
        }

        .summary-table {
            width: 100%;
            margin-top: 5px;
        }

        .summary-table td {
            padding: 2px 0;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 11px;
        }

        .thank-you {
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
        }

        .contact-info {
            text-align: center;
            margin-top: 5px;
            font-size: 11px;
        }
    </style>
</head>

<body >
    <div class="container">
        <!-- Logo and Store Name -->
        <div class="logo-text">{{ strtoupper($store->store_name) }}</div>
        <div class="store-name">{{ $store->store_name }}</div>

        <!-- Receipt Information -->
        <div class="receipt-info">
            <table>
                <tr>
                    <td>No Nota</td>
                    <td>: {{ $order->order_number }}</td>
                </tr>
                <tr>
                    <td>Waktu</td>
                    <td>: {{ $order->created_at->format('d M y H:i') }}</td>
                </tr>
                <tr>
                    <td>Kasir</td>
                    <td>: {{ $order->user ? $order->user->name : 'Kasir' }}</td>
                </tr>
                <tr>
                    <td>Nama Order</td>
                    <td>: {{ $order->customer_name ?? $order->id }}</td>
                </tr>
            </table>
        </div>

        <div class="dashed-line"></div>

        <!-- Order Items -->
        <table class="order-table">
            @foreach($order->orderItems as $item)
                <tr>
                    <td>{{ $item->quantity }} {{ $item->product->name }}</td>
                    <td class="right">{{ number_format($item->subtotal, 0, ',', ',') }}</td>
                </tr>
                @if($item->notes)
                    <tr>
                        <td>&nbsp;&nbsp;+ {{ $item->notes }}</td>
                        <td></td>
                    </tr>
                @endif
            @endforeach
        </table>

        <div class="dashed-line"></div>

        <!-- Totals -->
        <table class="summary-table">
            <tr>
                <td>Subtotal {{ $order->orderItems->sum('quantity') }} Produk</td>
                <td class="right">{{ number_format($order->total_amount, 0, ',', ',') }}</td>
            </tr>
            @if($order->tax_amount > 0)
                <tr>
                    <td>Pajak</td>
                    <td class="right">{{ number_format($order->tax_amount, 0, ',', ',') }}</td>
                </tr>
            @endif
            @if($order->discount_amount > 0)
                <tr>
                    <td>Diskon</td>
                    <td class="right">{{ number_format($order->discount_amount, 0, ',', ',') }}</td>
                </tr>
            @endif
            <tr class="font-bold">
                <td>Total Tagihan</td>
                <td class="right">{{ number_format($order->final_amount, 0, ',', ',') }}</td>
            </tr>

            <!-- Informasi Pembayaran -->
            <tr>
                <td colspan="2" class="pt-2 border-t">Pembayaran:</td>
            </tr>
            <tr>
                <td>Metode</td>
                <td class="right">{{ $order->paymentMethod->name }}</td>
            </tr>
            @if($order->paymentMethod->id == 1) {{-- Jika pembayaran cash --}}
                <tr>
                    <td>Uang Diterima</td>
                    <td class="right">{{ number_format($order->cash_amount, 0, ',', ',') }}</td>
                </tr>
                <tr class="font-bold">
                    <td>Kembalian</td>
                    <td class="right">{{ number_format($order->cash_change, 0, ',', ',') }}</td>
                </tr>
            @endif
        </table>

        <div class="dashed-line"></div>

        <!-- Thank You Message -->
        <div class="thank-you">{{ $store->footer_message ?? 'Terima Kasih!' }}</div>

        <!-- Contact Information -->
        <div class="contact-info">
            @if($store->phone)
                <p>CP: {{ $store->phone }}</p>
            @endif
            @if($store->social_media)
                <p>Instagram: {{ $store->social_media }}</p>
            @endif
        </div>

        <!-- Payment Time -->
        <div class="footer">
            <p>Terbayar {{ $order->created_at->format('d M y H:i') }}</p>
            <p>dicetak: {{ $order->user ? $order->user->name : 'Kasir' }}</p>
        </div>
    </div>
</body>

</html>