<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'customer_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                    'notes' => $item['notes'] ?? null,
                ];

                $product->decrement('stock', $item['quantity']);
            }

            // Ambil pajak dari store_settings atau default ke 0%
            $storeSetting = StoreSetting::first();
            $taxPercentage = $storeSetting ? $storeSetting->tax_percentage : 0;

            $taxAmount = ($totalAmount * $taxPercentage);
            $discountAmount = $validated['discount_amount'] ?? 0;
            $finalAmount = $totalAmount + $taxAmount - $discountAmount;

            $order = Order::create([
                'total_amount' => $totalAmount,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'payment_method_id' => $validated['payment_method_id'],
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'user_id' => auth()->id(),
            ]);

            $order->orderItems()->createMany($orderItems);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat',
                'order' => $order->load(['orderItems.product', 'paymentMethod', 'user:id,name']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getOrderHistory()
    {
        $orders = Order::with(['paymentMethod', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return array_merge($order->toArray(), [
                    'cashier_name' => $order->user ? $order->user->name : 'Unknown'
                ]);
            });

        return response()->json($orders);
    }

    public function getOrder($id)
    {
        $order = Order::with(['orderItems.product', 'paymentMethod', 'user:id,name'])
            ->findOrFail($id);

        $orderData = $order->toArray();
        $orderData['cashier_name'] = $order->user ? $order->user->name : 'Unknown';

        return response()->json($orderData);
    }

    public function cancelOrder($id)
    {
        try {
            DB::beginTransaction();

            $order = Order::with('orderItems.product')->findOrFail($id);

            if ($order->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya order dengan status completed yang dapat dibatalkan',
                ], 400);
            }

            $updatedProducts = [];
            foreach ($order->orderItems as $item) {
                $item->product->increment('stock', $item->quantity);
                $updatedProducts[] = Product::find($item->product_id);
            }

            $order->status = 'cancelled';
            $order->cancelled_at = now();
            $order->cancelled_by = auth()->id();
            $order->save();

            DB::commit();

            $order = $order->fresh(['orderItems.product', 'paymentMethod', 'user:id,name']);

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibatalkan',
                'order' => $order,
                'updatedProducts' => $updatedProducts
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}