<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-6 py-3 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">POS System</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700">
                    {{ auth()->user()->name }} ({{ auth()->user()->getRoleName() }})
                </span>
                <form method="POST" action="{{ route('pos.logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
                @if(auth()->user()->isAdmin())
                <a href="/admin" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-cog mr-2"></i> Admin Dashboard
                </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex-1 flex overflow-hidden" x-data="posApp()">
        <!-- Products Section -->
        <div class="w-2/3 bg-white p-6 overflow-y-auto">
            <div class="mb-6 flex justify-between">
                <div class="flex space-x-4">
                    <div>
                        <select x-model="selectedCategory" @change="filterProducts()" class="border p-2 rounded">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <input type="text" x-model="searchQuery" @input="filterProducts()" 
                            placeholder="Search products..." 
                            class="border p-2 pl-10 rounded w-64">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition cursor-pointer"
                         @click="addToCart(product)">
                        <div class="h-40 bg-gray-200 flex items-center justify-center overflow-hidden">
                            <img x-bind:src="product.image_path ? '/storage/' + product.image_path : '/images/no-image.png'" 
                            x-bind:alt="product.name"
                            class="w-full h-full object-cover">
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-gray-800" x-text="product.name"></h3>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-green-600 font-bold" x-text="formatCurrency(product.price)"></span>
                                <span class="text-sm text-gray-500" x-text="'Stock: ' + product.stock"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="w-1/3 bg-gray-50 p-6 overflow-y-auto border-l">
            <h2 class="text-xl font-bold mb-4">Current Order</h2>
            
            <!-- Cart Items -->
            <div class="mb-6">
                <template x-if="cart.length === 0">
                    <div class="text-center p-6 bg-white rounded-lg border">
                        <i class="fas fa-shopping-cart text-gray-400 text-5xl mb-3"></i>
                        <p class="text-gray-500">Cart is empty</p>
                    </div>
                </template>

                <template x-if="cart.length > 0">
                    <div>
                        <ul class="space-y-3">
                            <template x-for="(item, index) in cart" :key="index">
                                <li class="bg-white p-3 rounded-lg shadow-sm">
                                    <div class="flex justify-between">
                                        <div>
                                            <h4 class="font-medium" x-text="item.name"></h4>
                                            <div class="text-sm text-gray-500" x-text="formatCurrency(item.price) + ' x ' + item.quantity"></div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-medium" x-text="formatCurrency(item.price * item.quantity)"></div>
                                            <div class="flex items-center mt-1">
                                                <button @click="decreaseQuantity(index)" class="text-red-500 hover:bg-red-100 h-8 w-8 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <span class="mx-2" x-text="item.quantity"></span>
                                                <button @click="increaseQuantity(index)" class="text-green-500 hover:bg-green-100 h-8 w-8 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                                <button @click="removeFromCart(index)" class="ml-2 text-red-500 hover:bg-red-100 h-8 w-8 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <div class="mt-1">
                                                <input type="text" x-model="item.notes" placeholder="Add note..." class="border p-1 text-sm w-full rounded">
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </template>
            </div>

            <!-- Order Summary -->
            <template x-if="cart.length > 0">
                <div class="bg-white p-4 rounded-lg shadow-sm mb-4">
                    <h3 class="font-bold mb-3">Order Summary</h3>
                    
                    <div class="space-y-2 mb-3">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span x-text="formatCurrency(cartTotal)"></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span>Tax (%)</span>
                            <div class="w-20">
                                <input type="number" x-model.number="taxPercentage" min="0" class="border p-1 text-right w-full rounded">
                            </div>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Tax Amount</span>
                            <span x-text="formatCurrency(taxAmount)"></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span>Discount</span>
                            <div class="w-32">
                                <input type="number" x-model.number="discountAmount" min="0" class="border p-1 text-right w-full rounded">
                            </div>
                        </div>
                        
                        <div class="flex justify-between font-bold text-lg pt-2 border-t">
                            <span>Total</span>
                            <span x-text="formatCurrency(finalAmount)"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Customer Name (Optional)
                        </label>
                        <input type="text" x-model="customerName" class="border p-2 w-full rounded">
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Payment Method
                        </label>
                        <select x-model="paymentMethodId" class="border p-2 w-full rounded">
                            <option value="">Select Payment Method</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Notes (Optional)
                        </label>
                        <textarea x-model="orderNotes" class="border p-2 w-full rounded" rows="2"></textarea>
                    </div>

                    <button @click="processOrder()" 
                        :disabled="isProcessing || cart.length === 0 || !paymentMethodId"
                        :class="{'opacity-50 cursor-not-allowed': isProcessing || cart.length === 0 || !paymentMethodId}"
                        class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded w-full flex items-center justify-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span x-text="isProcessing ? 'Processing...' : 'Complete Order'"></span>
                    </button>
                </div>
            </template>

            <!-- Success Modal -->
            <div x-show="showSuccessModal" 
                 class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="bg-white rounded-lg shadow-lg p-6 w-96 max-w-full" 
                     @click.away="showSuccessModal = false">
                    <div class="text-center mb-4">
                        <div class="bg-green-100 text-green-600 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check-circle text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">Order Completed</h3>
                        <p class="text-gray-600" x-text="'Order #: ' + lastOrderNumber"></p>
                    </div>
                    
                    <div class="mb-4">
                        <div class="bg-gray-50 p-3 rounded">
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600">Total Amount:</span>
                                <span class="font-bold" x-text="formatCurrency(lastOrderAmount)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payment Method:</span>
                                <span x-text="lastOrderPaymentMethod"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between">
                        <button @click="printReceipt()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                            <i class="fas fa-print mr-2"></i> Print Receipt
                        </button>
                        <button @click="closeSuccessModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded">
                            New Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function posApp() {
            return {
                products: @json($products),
                filteredProducts: @json($products),
                cart: [],
                selectedCategory: '',
                searchQuery: '',
                taxPercentage: 10,
                discountAmount: 0,
                customerName: '',
                paymentMethodId: '',
                orderNotes: '',
                isProcessing: false,
                showSuccessModal: false,
                lastOrderNumber: '',
                lastOrderAmount: 0,
                lastOrderPaymentMethod: '',
                
                init() {
                    // Set CSRF token for AJAX requests
                    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                },
                
                get cartTotal() {
                    return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
                },
                
                get taxAmount() {
                    return (this.cartTotal * this.taxPercentage) / 100;
                },
                
                get finalAmount() {
                    return this.cartTotal + this.taxAmount - this.discountAmount;
                },
                
                formatCurrency(amount) {
                    return 'Rp ' + amount.toLocaleString('id-ID');
                },
                
                filterProducts() {
                    this.filteredProducts = this.products.filter(product => {
                        const matchesCategory = !this.selectedCategory || product.category === this.selectedCategory;
                        const matchesSearch = !this.searchQuery || 
                            product.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                        return matchesCategory && matchesSearch;
                    });
                },
                
                addToCart(product) {
                    const existingItemIndex = this.cart.findIndex(item => item.product_id === product.id);
                    
                    if (existingItemIndex !== -1) {
                        // Item already in cart, increase quantity if stock allows
                        const currentQty = this.cart[existingItemIndex].quantity;
                        if (currentQty < product.stock) {
                            this.cart[existingItemIndex].quantity++;
                        } else {
                            alert('Maximum available stock reached');
                        }
                    } else {
                        // Add new item to cart
                        this.cart.push({
                            product_id: product.id,
                            name: product.name,
                            price: product.price,
                            quantity: 1,
                            notes: '',
                            max_quantity: product.stock
                        });
                    }
                },
                
                increaseQuantity(index) {
                    if (this.cart[index].quantity < this.cart[index].max_quantity) {
                        this.cart[index].quantity++;
                    } else {
                        alert('Maximum available stock reached');
                    }
                },
                
                decreaseQuantity(index) {
                    if (this.cart[index].quantity > 1) {
                        this.cart[index].quantity--;
                    } else {
                        this.removeFromCart(index);
                    }
                },
                
                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },
                
                processOrder() {
                    if (this.cart.length === 0) {
                        alert('Cart is empty');
                        return;
                    }
                    
                    if (!this.paymentMethodId) {
                        alert('Please select a payment method');
                        return;
                    }
                    
                    this.isProcessing = true;
                    
                    const orderData = {
                        items: this.cart.map(item => ({
                            product_id: item.product_id,
                            quantity: item.quantity,
                            notes: item.notes
                        })),
                        payment_method_id: this.paymentMethodId,
                        customer_name: this.customerName,
                        notes: this.orderNotes,
                        tax_percentage: this.taxPercentage,
                        discount_amount: this.discountAmount
                    };
                    
                    fetch('{{ route('pos.orders.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(orderData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.isProcessing = false;
                        
                        if (data.success) {
                            // Store the last order info for receipt
                            this.lastOrderNumber = data.order.order_number;
                            this.lastOrderAmount = data.order.final_amount;
                            this.lastOrderPaymentMethod = data.order.payment_method.name;
                            
                            // Show success modal
                            this.showSuccessModal = true;
                            
                            // Reset products stock in the UI
                            this.updateProductsStock(orderData.items);
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        this.isProcessing = false;
                        console.error('Error:', error);
                        alert('An error occurred while processing the order.');
                    });
                },
                
                updateProductsStock(orderItems) {
                    orderItems.forEach(item => {
                        const productIndex = this.products.findIndex(p => p.id === item.product_id);
                        if (productIndex !== -1) {
                            this.products[productIndex].stock -= item.quantity;
                        }
                    });
                    
                    // Update filtered products as well
                    this.filterProducts();
                },
                
                printReceipt() {
                    // Create a printable version of the receipt
                    const receiptWindow = window.open('', '_blank', 'width=400,height=600');
                    
                    if (receiptWindow) {
                        let receiptContent = `
                            <html>
                            <head>
                                <title>Receipt #${this.lastOrderNumber}</title>
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
                                    <div>Receipt #: ${this.lastOrderNumber}</div>
                                    <div>Date: ${new Date().toLocaleString()}</div>
                                    ${this.customerName ? `<div>Customer: ${this.customerName}</div>` : ''}
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
                        `;
                        
                        this.cart.forEach(item => {
                            receiptContent += `
                                <tr>
                                    <td>${item.name}</td>
                                    <td>${item.quantity}</td>
                                    <td>${this.formatCurrency(item.price)}</td>
                                    <td>${this.formatCurrency(item.price * item.quantity)}</td>
                                </tr>
                                ${item.notes ? `<tr><td colspan="4" style="font-size:10px;padding-top:0">Note: ${item.notes}</td></tr>` : ''}
                            `;
                        });
                        
                        receiptContent += `
                                        <tr>
                                            <td colspan="3" class="text-right">Subtotal:</td>
                                            <td>${this.formatCurrency(this.cartTotal)}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right">Tax (${this.taxPercentage}%):</td>
                                            <td>${this.formatCurrency(this.taxAmount)}</td>
                                        </tr>
                                        ${this.discountAmount > 0 ? `
                                        <tr>
                                            <td colspan="3" class="text-right">Discount:</td>
                                            <td>${this.formatCurrency(this.discountAmount)}</td>
                                        </tr>` : ''}
                                        <tr class="total-row">
                                            <td colspan="3" class="text-right">TOTAL:</td>
                                            <td>${this.formatCurrency(this.finalAmount)}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <div class="divider"></div>
                                
                                <div>
                                    <div>Payment Method: ${this.lastOrderPaymentMethod}</div>
                                    ${this.orderNotes ? `<div>Notes: ${this.orderNotes}</div>` : ''}
                                </div>
                                
                                <div class="footer">
                                    <p>Thank you for your business!</p>
                                </div>
                            </body>
                            </html>
                        `;
                        
                        receiptWindow.document.open();
                        receiptWindow.document.write(receiptContent);
                        receiptWindow.document.close();
                        receiptWindow.print();
                    }
                },
                
                closeSuccessModal() {
                    this.showSuccessModal = false;
                    // Clear cart and reset form
                    this.cart = [];
                    this.customerName = '';
                    this.paymentMethodId = '';
                    this.orderNotes = '';
                }
            };
        }
    </script>
</body>
</html>