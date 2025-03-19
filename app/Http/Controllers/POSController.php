<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

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

    public function showReceipt($id)
    {
        $order = Order::with(['orderItems.product', 'paymentMethod', 'user'])
            ->findOrFail($id);

        $store = StoreSetting::first() ?? new StoreSetting([
            'store_name' => 'Your Store',
            'address_line_1' => 'Store Address Line 1',
            'address_line_2' => 'Store Address Line 2',
            'phone' => '(123) 456-7890',
            'footer_message' => 'Thank you for your business!'
        ]);

        return view('pos.receipt', compact('order', 'store'));
    }
}