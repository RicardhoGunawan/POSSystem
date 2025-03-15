<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\StoreSetting;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class POSController extends Controller
{
    public function index()
    {
        $products = Product::where('is_available', true)
            ->where('stock', '>', 0)
            ->get();
        $categories = Category::select('id', 'name')->get();
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
                    $index = explode('.', $attribute)[1];
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

            // Create order with user_id
            $order = Order::create([
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'payment_method_id' => $validated['payment_method_id'],
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'user_id' => auth()->id(), // Menambahkan user_id dari user yang sedang login
            ]);

            // Insert multiple order items at once
            $order->orderItems()->createMany($orderItems);

            DB::commit();

            // Load relations including user
            $order->load(['orderItems.product', 'paymentMethod', 'user:id,name']);

            // Add cashier_name to response
            $orderData = $order->toArray();
            $orderData['cashier_name'] = $order->user ? $order->user->name : 'Unknown';

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat',
                'order' => $orderData,
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
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }

        if ($search) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        $products = $query->get();

        return response()->json($products);
    }

    /**
     * Show the receipt for a specific order
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function showReceipt($id)
    {
        $order = Order::with(['orderItems.product', 'paymentMethod', 'user'])
            ->findOrFail($id);

        // Get store settings or create default if not exists
        $store = StoreSetting::first();
        if (!$store) {
            $store = new StoreSetting([
                'store_name' => 'Your Store',
                'address_line_1' => 'Store Address Line 1',
                'address_line_2' => 'Store Address Line 2',
                'phone' => '(123) 456-7890',
                'footer_message' => 'Thank you for your business!'
            ]);
        }

        return view('pos.receipt', compact('order', 'store'));
    }

    /**
     * Get order history
     *
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Get a specific order with details
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrder($id)
    {
        $order = Order::with(['orderItems.product', 'paymentMethod', 'user:id,name'])
            ->findOrFail($id);

        $orderData = $order->toArray();
        $orderData['cashier_name'] = $order->user ? $order->user->name : 'Unknown';

        return response()->json($orderData);
    }
    /**
     * Cancel an order
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrder($id)
    {
        try {
            DB::beginTransaction();

            $order = Order::with('orderItems.product')->findOrFail($id);

            // Check if order can be cancelled (only completed orders can be cancelled)
            if ($order->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya order dengan status completed yang dapat dibatalkan',
                ], 400);
            }

            // Restore product stock
            $updatedProducts = [];
            foreach ($order->orderItems as $item) {
                $item->product->increment('stock', $item->quantity);
                // Collect updated product data
                $updatedProducts[] = Product::find($item->product_id);
            }

            // Update order status
            $order->status = 'cancelled';
            $order->cancelled_at = now();
            $order->cancelled_by = auth()->id();
            $order->save();

            DB::commit();

            // Load fresh data with relationships
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