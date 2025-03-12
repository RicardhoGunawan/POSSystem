<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Collection;

class PosSystem extends Component
{
    public $cart = [];
    public $products = [];
    public $paymentMethods = [];
    public $searchQuery = '';
    public $selectedCategory = '';
    public $categories = [];
    
    // Customer information
    public $customerName = '';
    public $notes = '';
    public $selectedPaymentMethod = null;
    
    // Amounts
    public $totalAmount = 0;
    public $taxAmount = 0;
    public $discountAmount = 0;
    public $finalAmount = 0;
    
    // Tax and discount rates
    public $taxRate = 10; // 10% tax
    public $discountRate = 0;
    
    protected $rules = [
        'customerName' => 'required|string|min:3',
        'selectedPaymentMethod' => 'required|exists:payment_methods,id',
    ];

    public function mount()
    {
        $this->loadProducts();
        $this->loadPaymentMethods();
        $this->loadCategories();
    }

    public function loadProducts()
    {
        $query = Product::where('is_available', true);
        
        if ($this->searchQuery) {
            $query->where('name', 'like', "%{$this->searchQuery}%");
        }
        
        if ($this->selectedCategory) {
            $query->where('category', $this->selectedCategory);
        }
        
        $this->products = $query->get();
    }
    
    public function loadPaymentMethods()
    {
        $this->paymentMethods = PaymentMethod::where('is_active', true)->get();
        if ($this->paymentMethods->count() > 0 && !$this->selectedPaymentMethod) {
            $this->selectedPaymentMethod = $this->paymentMethods->first()->id;
        }
    }
    
    public function loadCategories()
    {
        $this->categories = Product::distinct('category')->pluck('category')->toArray();
    }

    public function updatedSearchQuery()
    {
        $this->loadProducts();
    }
    
    public function updatedSelectedCategory()
    {
        $this->loadProducts();
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        
        if (!$product || !$product->is_available) {
            return;
        }
        
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
            $this->cart[$productId]['subtotal'] = $this->cart[$productId]['quantity'] * $this->cart[$productId]['price'];
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'subtotal' => $product->price,
                'notes' => '',
            ];
        }
        
        $this->calculateTotals();
    }

    public function removeFromCart($productId)
    {
        if (isset($this->cart[$productId])) {
            unset($this->cart[$productId]);
            $this->calculateTotals();
        }
    }

    public function updateQuantity($productId, $quantity)
    {
        if (isset($this->cart[$productId])) {
            $quantity = max(1, intval($quantity));
            $this->cart[$productId]['quantity'] = $quantity;
            $this->cart[$productId]['subtotal'] = $quantity * $this->cart[$productId]['price'];
            $this->calculateTotals();
        }
    }
    
    public function updateNotes($productId, $notes)
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['notes'] = $notes;
        }
    }

    public function calculateTotals()
    {
        $this->totalAmount = collect($this->cart)->sum('subtotal');
        $this->taxAmount = $this->totalAmount * ($this->taxRate / 100);
        $this->discountAmount = $this->totalAmount * ($this->discountRate / 100);
        $this->finalAmount = $this->totalAmount + $this->taxAmount - $this->discountAmount;
    }
    
    public function updateDiscountRate($rate)
    {
        $this->discountRate = max(0, min(100, $rate));
        $this->calculateTotals();
    }

    public function resetCart()
    {
        $this->cart = [];
        $this->customerName = '';
        $this->notes = '';
        $this->discountRate = 0;
        $this->calculateTotals();
    }

    public function processOrder()
    {
        $this->validate();
        
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang belanja kosong!');
            return;
        }
        
        try {
            // Create order
            $order = Order::create([
                'total_amount' => $this->totalAmount,
                'tax_amount' => $this->taxAmount,
                'discount_amount' => $this->discountAmount,
                'final_amount' => $this->finalAmount,
                'payment_method_id' => $this->selectedPaymentMethod,
                'status' => 'completed',
                'notes' => $this->notes,
                'customer_name' => $this->customerName,
            ]);
            
            // Create order items
            foreach ($this->cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                    'notes' => $item['notes'],
                ]);
            }
            
            session()->flash('success', 'Pesanan berhasil dibuat dengan nomor: ' . $order->order_number);
            $this->resetCart();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pos-system');
    }
}