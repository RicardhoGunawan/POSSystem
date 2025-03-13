<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{    public function index()
    {
        $products = Product::where('is_available', true)
            ->where('stock', '>', 0)
            ->get();
        $categories = Product::select('category')->distinct()->pluck('category');
        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        return view('pos.index', compact('products', 'categories', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => [
                'required',
                'exists:products,id',
                function ($attribute, $value, $fail) use ($request) {
                    $product = Product::find($value);
                    $index = explode('.', $attribute)[1]; // Mendapatkan index dari items.*
                    $quantity = $request->items[$index]['quantity'] ?? 0;

                    if ($product && $product->stock < $quantity) {
                        $fail("Stock tidak cukup untuk produk: {$product->name}");
                    }
                }
            ],
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'customer_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'tax_percentage' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitPrice = $product->price;
                $subtotal = $unitPrice * $item['quantity'];
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'notes' => $item['notes'] ?? null,
                ];

                // Update stock
                $product->decrement('stock', $item['quantity']);
            }

            $taxAmount = isset($validated['tax_percentage']) 
                ? ($totalAmount * $validated['tax_percentage'] / 100) 
                : 0;

            $discountAmount = $validated['discount_amount'] ?? 0;
            $finalAmount = $totalAmount + $taxAmount - $discountAmount;

            // Create order
            $order = Order::create([
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'payment_method_id' => $validated['payment_method_id'],
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
            ]);

            // Insert multiple order items at once
            $order->orderItems()->createMany($orderItems);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat',
                'order' => $order->load('orderItems.product', 'paymentMethod'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getProducts(Request $request)
    {
        $category = $request->query('category');
        $search = $request->query('search');

        $query = Product::query()
            ->where('is_available', true)
            ->where('stock', '>', 0);

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        $products = $query->get();

        return response()->json($products);
    }
}
