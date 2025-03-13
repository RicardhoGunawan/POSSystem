<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; margin: 0; padding: 20px; }
        h1 { font-size: 16px; text-align: center; margin-bottom: 10px; }
        .store-info { text-align: center; margin-bottom: 20px; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 5px; text-align: left; }
        th { border-bottom: 1px solid #000; }
        .total-row td { border-top: 1px solid #000; padding-top: 5px; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; }
        @media print {
            body { padding: 0; }
            button { display: none; }
        }
    </style>
</head>
<body>
    <div class="store-info">
        <h1>RECEIPT</h1>
        <div>Your Store Name</div>
        <div>Store Address Line 1</div>
        <div>Store Address Line 2</div>
        <div>Phone: (123) 456-7890</div>
    </div>
    
    <div>
        <div>Receipt #: {{ $order->order_number }}</div>
        <div>Date: {{ $order->created_at->format('Y-m-d H:i:s') }}</div>
        @if($order->customer_name)
        <div>Customer: {{ $order->customer_name }}</div>
        @endif
    </div>
    
    <div class="divider"></div>
    
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
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
                <td colspan="4" style="font-size:10px;padding-top:0">Note: {{ $item->notes }}</td>
            </tr>
            @endif
            @endforeach
            <tr>
                <td colspan="3" class="text-right">Subtotal:</td>
                <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right">Tax:</td>
                <td>Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
            </tr>
            @if($order->discount_amount > 0)
            <tr>
                <td colspan="3" class="text-right">Discount:</td>
                <td>Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL:</td>
                <td>Rp {{ number_format($order->final_amount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    
    <div class="divider"></div>
    
    <div>
        <div>Payment Method: {{ $order->paymentMethod->name }}</div>
        @if($order->notes)
        <div>Notes: {{ $order->notes }}</div>
        @endif
    </div>
    
    <div class="footer">
        <p>Thank you for your business!</p>
    </div>
    
    <div style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()">Print Receipt</button>
    </div>
</body>
</html>