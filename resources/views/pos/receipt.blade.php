<!DOCTYPE html>
<html lang="en">
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
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
            max-width: 80mm;
        }

        .container {
            padding: 10px;
        }

        .store-info {
            text-align: center;
            margin-bottom: 15px;
        }

        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .receipt-header {
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
        }

        .receipt-table th,
        .receipt-table td {
            padding: 5px;
            font-size: 11px;
        }

        .receipt-table th {
            border-bottom: 1px solid #000;
            text-align: left;
        }

        .item-note {
            font-size: 10px;
            font-style: italic;
            color: #666;
            padding-left: 5px;
        }

        .total-section {
            border-top: 1px solid #000;
            font-weight: bold;
        }

        .payment-info {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
        }

        .print-button {
            text-align: center;
            margin-top: 20px;
        }

        .print-button button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .print-button button:hover {
            background-color: #45a049;
        }

        @media print {
            body {
                padding: 0;
            }

            .print-button {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Store Info -->
        <div class="store-info">
            <div class="store-name">{{ $store->store_name }}</div>
            <div>{{ $store->address_line_1 }}</div>
            @if($store->address_line_2)
                <div>{{ $store->address_line_2 }}</div>
            @endif
            <div>Tel: {{ $store->phone }}</div>
        </div>

        <!-- Receipt Header -->
        <div class="receipt-header">
            <div style="text-align: center; font-weight: bold; margin-bottom: 10px;">RECEIPT</div>
            <table style="width: 100%">
                <tr>
                    <td>Receipt:</td>
                    <td>{{ $order->order_number }}</td>
                </tr>
                <tr>
                    <td>Date:</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @if($order->customer_name)
                <tr>
                    <td>Customer:</td>
                    <td>{{ $order->customer_name }}</td>
                </tr>
                @endif
                <tr>
                    <td>Cashier:</td>
                    <td>{{ $order->user ? $order->user->name : 'Unknown' }}</td>
                </tr>
            </table>
        </div>

        <!-- Order Items -->
        <table class="receipt-table">
            <thead>
                <tr>
                    <th style="width: 40%">Item</th>
                    <th style="width: 15%">Qty</th>
                    <th style="width: 20%">Price</th>
                    <th style="width: 25%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if($item->notes)
                        <tr>
                            <td colspan="4" class="item-note">Note: {{ $item->notes }}</td>
                        </tr>
                    @endif
                @endforeach

                <tr>
                    <td colspan="3" style="text-align: right">Subtotal:</td>
                    <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: right">Tax:</td>
                    <td>Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
                </tr>
                @if($order->discount_amount > 0)
                    <tr>
                        <td colspan="3" style="text-align: right">Discount:</td>
                        <td>Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="total-section">
                    <td colspan="3" style="text-align: right">TOTAL:</td>
                    <td>Rp {{ number_format($order->final_amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Payment Info -->
        <div class="payment-info">
            <div>Payment Method: {{ $order->paymentMethod->name }}</div>
            @if($order->notes)
                <div style="margin-top: 5px;">Notes: {{ $order->notes }}</div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>{{ $store->footer_message }}</p>
        </div>

        <!-- Print Button -->
        <!-- <div class="print-button">
            <button onclick="window.print()">Print Receipt</button>
        </div> -->
    </div>
</body>
</html>
