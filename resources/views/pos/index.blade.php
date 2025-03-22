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
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</head>
<body class="bg-gray-100 h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-30">
        <div class="container mx-auto px-4 py-4 flex flex-wrap justify-between items-center">
            <!-- Logo & Title -->
            <div class="flex items-center space-x-3">
                <a href="/pos" class="flex items-center transition-transform transform hover:scale-105">
                    <i class="fas fa-shopping-cart text-blue-600 text-3xl mr-2"></i>
                    <h1 class="text-2xl font-bold text-gray-800 hover:text-blue-600 transition-colors duration-300">POS System</h1>
                </a>


                <span class="hidden md:inline-block px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                    <i class="fas fa-calendar-alt mr-1"></i> <span id="current-date"></span>
                </span>
            </div>

            <!-- User Info & Dropdown -->
            <div class="relative" x-data="{ dropdown: false }">
                <!-- Button User -->
                <button @click="dropdown = !dropdown" type="button"
                    class="flex items-center space-x-2 bg-gray-100 hover:bg-gray-200 rounded-lg py-2 px-3 transition duration-300">
                    <i class="fas fa-user-circle text-gray-600 text-lg"></i>
                    <span class="text-gray-700 font-medium">{{ auth()->user()->name }}</span>
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ auth()->user()->getRoleName() }}</span>
                    <i class="fas fa-chevron-down text-gray-500 text-sm"></i>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="dropdown" @click.away="dropdown = false" 
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 bg-white divide-y divide-gray-100 rounded-lg shadow-md w-44 z-50">
                    <ul class="py-2 text-sm text-gray-700">
                        <!-- Order History Button inside Dropdown -->
                        <li>
                            <button 
                                @click="$dispatch('toggle-order-history'); dropdown = false"
                                class="block w-full text-left px-4 py-2 hover:bg-gray-100"
                                type="button"
                            >
                                <i class="fas fa-history mr-2"></i> Order History
                            </button>
                        </li>
                        <div class="border-t my-1"></div>
                        
                        @if(auth()->user()->isAdmin())
                        <li>
                            <a href="/admin" class="block px-4 py-2 hover:bg-gray-100">Admin Panel</a>
                        </li>
                        <div class="border-t my-1"></div>
                        @endif
                        
                        <li>
                            <form method="POST" action="{{ route('pos.logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>
    <!-- Main Content -->
    <div class="flex-1 flex flex-col md:flex-row overflow-hidden" x-data="posApp()">
        <!-- Products Section -->
        <div class="w-full md:w-2/3 bg-white p-6 overflow-y-auto">
            <div class="mb-6 bg-gradient-to-r from-blue-50 to-purple-50 p-4 rounded-lg shadow-sm">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <!-- Filter Section -->
                    <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-4 w-full md:w-auto">
                        <!-- Category Dropdown -->
                        <div class="w-full md:w-48">
                            <select 
                                x-model="selectedCategory" 
                                @change="filterProducts()" 
                                class="w-full border border-gray-300 p-2 pl-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                            >
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Search Input -->
                        <div class="relative w-full md:w-64">
                            <input 
                                type="text" 
                                x-model="searchQuery" 
                                @input="filterProducts()" 
                                placeholder="Search products..." 
                                class="w-full border border-gray-300 p-2 pl-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                            >
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>

            <template x-if="isLoadingProducts">
                <div class="flex justify-center items-center h-40">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-500"></i>
                </div>
            </template>

            <div x-show="!showOrderHistory" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-transform transform hover:scale-105 cursor-pointer" @click="addToCart(product)">
                        <div class="h-40 bg-gray-200 flex items-center justify-center overflow-hidden">
                            <img x-bind:src="product.image_path ? '/storage/' + product.image_path : '/images/no-image.png'" x-bind:alt="product.name" class="w-full h-full object-cover">
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

            <!-- Order History View -->
            <div x-show="showOrderHistory" class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Header Section -->
                <div class="p-6 border-b border-gray-200 flex flex-col md:flex-row justify-between items-center">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">Order History</h2>
                    <div class="flex items-center space-x-4">
                        <!-- Search Input -->
                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="orderSearchQuery" 
                                @input="filterOrders()" 
                                placeholder="Search orders..." 
                                class="border border-gray-300 p-2 pl-10 rounded-lg w-full md:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                        <!-- Refresh Button -->
                        <button 
                            @click="loadOrders()" 
                            class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg transition-transform transform hover:scale-105"
                        >
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <template x-if="isLoadingOrders">
                    <div class="flex justify-center items-center h-40">
                        <i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i>
                    </div>
                </template>

                <!-- Empty State -->
                <template x-if="orders.length === 0 && !isLoadingOrders">
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-box-open text-4xl mb-3"></i>
                        <p class="text-lg">No orders found.</p>
                    </div>
                </template>

                <!-- Order Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-50">
                            <tr class="text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">Cashier</th>
                                <th class="py-3 px-6 text-left">Order #</th>
                                <th class="py-3 px-6 text-left">Date</th>
                                <th class="py-3 px-6 text-left">Customer</th>
                                <th class="py-3 px-6 text-right">Total</th>
                                <th class="py-3 px-6 text-center">Payment</th>
                                <th class="py-3 px-6 text-center">Status</th>
                                <th class="py-3 px-6 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <template x-if="orders.length === 0 && !isLoadingOrders">
                                <tr>
                                    <td colspan="8" class="py-4 px-6 text-center text-gray-500">No orders found.</td>
                                </tr>
                            </template>
                            <template x-for="order in filteredOrders" :key="order.id">
                                <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors duration-200">
                                    <!-- Cashier -->
                                    <td class="py-4 px-6 text-left" x-text="order.cashier_name || 'Unknown'"></td>
                                    <!-- Order Number -->
                                    <td class="py-4 px-6 text-left font-medium" x-text="order.order_number"></td>
                                    <!-- Date -->
                                    <td class="py-4 px-6 text-left" x-text="formatDate(order.created_at)"></td>
                                    <!-- Customer -->
                                    <td class="py-4 px-6 text-left" x-text="order.customer_name || 'Walk-in Customer'"></td>
                                    <!-- Total -->
                                    <td class="py-4 px-6 text-right font-semibold" x-text="formatCurrency(order.final_amount)"></td>
                                    <!-- Payment Method -->
                                    <td class="py-4 px-6 text-center" x-text="order.payment_method.name"></td>
                                    <!-- Status -->
                                    <td class="py-4 px-6 text-center">
                                        <span 
                                            class="py-1 px-3 rounded-full text-xs font-semibold" 
                                            :class="{
                                                'bg-green-100 text-green-700': order.status === 'completed',
                                                'bg-red-100 text-red-700': order.status === 'cancelled',
                                                'bg-yellow-100 text-yellow-700': order.status === 'pending'
                                            }" 
                                            x-text="order.status"
                                        ></span>
                                    </td>
                                    <!-- Actions -->
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center space-x-4">
                                            <!-- View Details -->
                                            <button 
                                                @click="viewOrderDetails(order)" 
                                                class="text-blue-500 hover:text-blue-600 transition-transform transform hover:scale-110"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <!-- Print Receipt -->
                                            <button 
                                                @click="printOrderReceipt(order.id)" 
                                                class="text-green-500 hover:text-green-600 transition-transform transform hover:scale-110"
                                            >
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <!-- Cancel Order (if completed) -->
                                            <template x-if="order.status === 'completed'">
                                                <button 
                                                    @click="showCancelConfirmation(order)" 
                                                    class="text-red-500 hover:text-red-600 transition-transform transform hover:scale-110"
                                                >
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cart Section -->
        <!-- Cart Section -->
        <div class="w-full md:w-1/3 bg-gray-50 p-6 overflow-y-auto border-l">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Current Order</h2>
            
            <div class="mb-6">
                <template x-if="cart.length === 0">
                    <div class="text-center p-6 bg-white rounded-lg border border-gray-200">
                        <i class="fas fa-shopping-cart text-gray-400 text-6xl mb-4"></i>
                        <p class="text-gray-500 text-lg">Your cart is empty</p>
                    </div>
                </template>

                <template x-if="cart.length > 0">
                    <div>
                        <ul class="space-y-4">
                            <template x-for="(item, index) in cart" :key="index">
                                <li class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <!-- Product Image -->
                                            <img :src="item.image" :alt="item.name" class="w-16 h-16 object-cover rounded-lg mr-4">
                                            <div>
                                                <h4 class="font-semibold text-gray-800" x-text="item.name"></h4>
                                                <div class="text-sm text-gray-500" x-text="formatCurrency(item.price) + ' x ' + item.quantity"></div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-medium text-gray-800" x-text="formatCurrency(item.price * item.quantity)"></div>
                                            <div class="flex items-center mt-2 space-x-2">
                                                <button @click="decreaseQuantity(index)" class="text-red-500 hover:bg-red-100 h-8 w-8 rounded-full flex items-center justify-center transition-colors duration-200">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <span class="mx-2 text-gray-700" x-text="item.quantity"></span>
                                                <button @click="increaseQuantity(index)" class="text-green-500 hover:bg-green-100 h-8 w-8 rounded-full flex items-center justify-center transition-colors duration-200">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                                <button @click="removeFromCart(index)" class="text-red-500 hover:bg-red-100 h-8 w-8 rounded-full flex items-center justify-center transition-colors duration-200">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <div class="mt-2">
                                                <input type="text" x-model="item.notes" placeholder="Add note..." class="border border-gray-300 p-2 text-sm w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                        <!-- Clear Cart Button -->
                        <div class="mt-6">
                            <button @click="clearCart" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg w-full flex items-center justify-center transition-colors duration-200">
                                <i class="fas fa-trash mr-2"></i>
                                <span>Clear Cart</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <template x-if="cart.length > 0">
                <div class="bg-white p-6 rounded-lg shadow-sm mb-6">
                    <h3 class="font-bold text-xl mb-4 text-gray-800">Order Summary</h3>
                    
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between text-gray-700">
                            <span>Subtotal</span>
                            <span x-text="formatCurrency(cartTotal)"></span>
                        </div>
                        
                        <div class="flex justify-between items-center text-gray-700">
                            <span>Tax (%)</span>
                            <div class="w-20">
                                <input type="text" 
                                    x-bind:value="taxPercentage + '%'" 
                                    disabled 
                                    class="border border-gray-300 p-2 text-right w-full rounded-lg bg-gray-100">
                            </div>
                        </div>
                        
                        <div class="flex justify-between text-gray-700">
                            <span>Tax Amount</span>
                            <span x-text="formatCurrency(taxAmount)"></span>
                        </div>
                        
                        <div class="flex justify-between items-center text-gray-700">
                            <span>Discount</span>
                            <div class="w-32">
                                <input type="number" x-model.number="discountAmount" min="0" class="border border-gray-300 p-2 text-right w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div class="flex justify-between font-bold text-lg pt-3 border-t border-gray-200 text-gray-800">
                            <span>Total</span>
                            <span x-text="formatCurrency(finalAmount)"></span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Customer Name (Optional)</label>
                        <input type="text" x-model="customerName" class="border border-gray-300 p-2 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Pilihan Metode Pembayaran -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Payment Method</label>
                        <select x-model="paymentMethodId" 
                                class="border border-gray-300 p-2 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Payment Method</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Numpad hanya muncul jika metode pembayaran adalah Cash -->
                    <template x-if="paymentMethodId == 1">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Enter Cash Amount</label>
                            <input type="text" 
                                x-model="formattedCashAmount"
                                @keydown="handleKeyDown($event)"
                                @input="formatCashInput"
                                @focus="$event.target.select()"
                                class="border border-gray-300 p-2 w-full rounded-lg text-right text-xl font-bold focus:outline-none focus:ring-2 focus:ring-blue-500">

                            <!-- Numpad -->
                            <div class="grid grid-cols-3 gap-2 mt-2">
                                <template x-for="num in [1,2,3,4,5,6,7,8,9,0]">
                                    <button @click="appendNumber(num)" 
                                            class="p-4 text-xl bg-gray-200 rounded-lg hover:bg-gray-300 active:bg-gray-400 transition-colors duration-150 transform active:scale-95">
                                        <span x-text="num"></span>
                                    </button>
                                </template>
                                <button @click="clearCashAmount()" 
                                        class="p-4 text-xl bg-red-500 text-white rounded-lg hover:bg-red-600 active:bg-red-700 transition-colors duration-150 transform active:scale-95">
                                    C
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Menampilkan kembalian -->
                    <template x-if="paymentMethodId == 1 && Number(cashAmount) >= finalAmount">
                        <div class="text-green-600 font-bold text-xl mt-4">
                            Change: <span x-text="formatCurrency(Number(cashAmount) - finalAmount)"></span>
                        </div>
                    </template>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Notes (Optional)</label>
                        <textarea x-model="orderNotes" class="border border-gray-300 p-2 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3"></textarea>
                    </div>

                    <button @click="processOrder()" 
                            :disabled="isProcessing || cart.length === 0 || !paymentMethodId || (paymentMethodId == 1 && Number(cashAmount) < finalAmount)" 
                            :class="{'opacity-50 cursor-not-allowed': isProcessing || cart.length === 0 || !paymentMethodId || (paymentMethodId == 1 && Number(cashAmount) < finalAmount)}" 
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg w-full flex items-center justify-center transition-colors duration-200">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span x-text="isProcessing ? 'Processing...' : 'Complete Order'"></span>
                    </button>
                </div>
            </template>

            <!-- Success Modal -->
            <div x-show="showSuccessModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="bg-white rounded-lg shadow-lg p-6 w-96 max-w-full" @click.away="showSuccessModal = false">
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
                        <button @click="printReceipt()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center transition-colors duration-200">
                            <i class="fas fa-print mr-2"></i> Print Receipt
                        </button>
                        <button @click="closeSuccessModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg transition-colors duration-200">
                            New Order
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Order Details Modal -->
            <div x-show="showOrderDetailsModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" 
                x-transition:enter="transition ease-out duration-300" 
                x-transition:enter-start="opacity-0" 
                x-transition:enter-end="opacity-100" 
                x-transition:leave="transition ease-in duration-200" 
                x-transition:leave-start="opacity-100" 
                x-transition:leave-end="opacity-0" 
                @click.self="showOrderDetailsModal = false">
                
                <div class="bg-white rounded-lg shadow-lg p-4 w-full max-w-[90%] sm:max-w-2xl max-h-[80vh] overflow-y-auto">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="text-lg font-bold text-gray-800" x-text="'Order Details: ' + selectedOrder.order_number"></h3>
                        <button @click="showOrderDetailsModal = false" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <!-- Order & Customer Info Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Left Column: Order Information -->
                        <div class="bg-gray-50 p-3 rounded-lg shadow-sm">
                            <h4 class="font-semibold text-gray-700 text-sm border-b pb-1 mb-2">Order Information</h4>
                            <div class="space-y-2 text-xs text-gray-600">
                                <div class="flex justify-between">
                                    <span>Date:</span>
                                    <span x-text="formatDate(selectedOrder.created_at)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Status:</span>
                                    <span class="py-1 px-2 rounded-full text-xs font-semibold" 
                                        :class="{
                                            'bg-green-100 text-green-700': selectedOrder.status === 'completed',
                                            'bg-yellow-100 text-yellow-700': selectedOrder.status === 'pending',
                                            'bg-red-100 text-red-700': selectedOrder.status === 'cancelled'
                                        }" 
                                        x-text="selectedOrder.status">
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Cashier:</span>
                                    <span x-text="selectedOrder.cashier_name || 'Unknown'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Customer Information -->
                        <div class="bg-gray-50 p-3 rounded-lg shadow-sm">
                            <h4 class="font-semibold text-gray-700 text-sm border-b pb-1 mb-2">Customer Information</h4>
                            <div class="space-y-2 text-xs text-gray-600">
                                <div class="flex justify-between">
                                    <span>Name:</span>
                                    <span x-text="selectedOrder.customer_name || 'Walk-in Customer'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Notes:</span>
                                    <span x-text="selectedOrder.notes || '-'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items Table -->
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-700 text-sm mb-2">Order Items</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full bg-white border border-gray-200 rounded-lg text-xs">
                                <thead>
                                    <tr class="bg-gray-100 text-gray-700 uppercase">
                                        <th class="py-2 px-3 text-left">Product</th>
                                        <th class="py-2 px-3 text-center">Qty</th>
                                        <th class="py-2 px-3 text-right">Unit Price</th>
                                        <th class="py-2 px-3 text-right">Subtotal</th>
                                        <th class="py-2 px-3 text-left">Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600">
                                    <template x-for="item in selectedOrder.order_items" :key="item.id">
                                        <tr class="border-t border-gray-200 hover:bg-gray-50 transition-colors duration-200">
                                            <td class="py-2 px-3 text-left" x-text="item.product.name"></td>
                                            <td class="py-2 px-3 text-center" x-text="item.quantity"></td>
                                            <td class="py-2 px-3 text-right" x-text="formatCurrency(item.unit_price)"></td>
                                            <td class="py-2 px-3 text-right font-medium" x-text="formatCurrency(item.subtotal)"></td>
                                            <td class="py-2 px-3 text-left" x-text="item.notes || '-'"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Combined Order Summary & Payment Information Card -->
                    <div class="bg-gray-50 p-3 rounded-lg shadow-sm mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Left Column: Order Summary -->
                            <div>
                                <h4 class="font-semibold text-gray-700 text-sm border-b pb-1 mb-2">Order Summary</h4>
                                <div class="space-y-2 text-xs text-gray-600">
                                    <div class="flex justify-between">
                                        <span>Subtotal:</span>
                                        <span x-text="formatCurrency(selectedOrder.total_amount ?? 0)"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Tax:</span>
                                        <span x-text="formatCurrency(selectedOrder.tax_amount ?? 0)"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Discount:</span>
                                        <span x-text="formatCurrency(selectedOrder.discount_amount ?? 0)"></span>
                                    </div>
                                    <div class="flex justify-between font-semibold text-sm border-t pt-2 text-gray-800">
                                        <span>Total:</span>
                                        <span x-text="formatCurrency(selectedOrder.final_amount ?? 0)"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column: Payment Information -->
                            <div>
                                <h4 class="font-semibold text-gray-700 text-sm border-b pb-1 mb-2">Payment Information</h4>
                                <div class="space-y-2 text-xs text-gray-600">
                                    <div class="flex justify-between">
                                        <span>Method:</span>
                                        <span x-text="selectedOrder.payment_method?.name || '-'"></span>
                                    </div>
                                    
                                    <!-- Cash payment details - shown only for cash payment method -->
                                    <template x-if="selectedOrder.payment_method_id == 1">
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span>Cash Amount:</span>
                                                <span x-text="formatCurrency(selectedOrder.cash_amount || 0)"></span>
                                            </div>
                                            <div class="flex justify-between font-semibold text-green-600">
                                                <span>Change:</span>
                                                <span x-text="formatCurrency(selectedOrder.cash_change || 0)"></span>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    <div class="flex justify-between font-semibold pt-2 text-gray-800">
                                        <span>Total Paid:</span>
                                        <span x-text="formatCurrency(selectedOrder.final_amount ?? 0)"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-2">
                        <button @click="printOrderReceipt(selectedOrder.id)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs flex items-center justify-center transition-colors duration-200">
                            <i class="fas fa-print mr-1"></i> Print Receipt
                        </button>
                        <button @click="showOrderDetailsModal = false" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1.5 rounded-lg text-xs transition-colors duration-200">
                            Close
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cancel Order Confirmation Modal -->
            <div x-show="showCancelOrderModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="bg-white rounded-lg shadow-lg p-6 w-96 max-w-full" @click.away="showCancelOrderModal = false">
                    <div class="text-center mb-4">
                        <div class="bg-red-100 text-red-600 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-exclamation-triangle text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">Konfirmasi Pembatalan Order</h3>
                        <p class="text-gray-600" x-text="'Order #: ' + (orderToCancel ? orderToCancel.id : '')"></p>
                    </div>
                    
                    <div class="mb-6">
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Tindakan ini akan:
                                    </p>
                                    <ul class="mt-1 text-sm text-yellow-700 list-disc list-inside">
                                        <li>Mengembalikan stok produk</li>
                                        <li>Menandai order sebagai dibatalkan</li>
                                        <li>Tidak dapat dikembalikan lagi</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 p-3 rounded">
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600">Total Amount:</span>
                                <span class="font-bold" x-text="orderToCancel ? formatCurrency(orderToCancel.final_amount) : '-'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payment Method:</span>
                                <span x-text="orderToCancel && orderToCancel.payment_method ? orderToCancel.payment_method.name : '-'"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between">
                        <button @click="showCancelOrderModal = false" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg transition-colors duration-200">
                            Kembali
                        </button>
                        <button @click="cancelOrder()" :disabled="isCancelling" :class="{'opacity-50 cursor-not-allowed': isCancelling}" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                            <template x-if="isCancelling">
                                <span class="flex items-center">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Processing...
                                </span>
                            </template>
                            <template x-if="!isCancelling">
                                <span>Ya, Batalkan Order</span>
                            </template>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Toast Notification -->
            <div class="fixed bottom-4 right-4 z-50" x-show="showToast" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2">
                <div class="bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span x-text="toastMessage"></span>
                    <button @click="showToast = false" class="ml-4 text-white hover:text-gray-100">
                        <i class="fas fa-times"></i>
                    </button>
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
                orders: [],
                filteredOrders: [],
                selectedCategory: '',
                searchQuery: '',
                orderSearchQuery: '',
                taxPercentage: 0, // Default diubah ke 0%
                discountAmount: 0,
                customerName: '',
                paymentMethodId: '',
                orderNotes: '',
                isProcessing: false,
                showSuccessModal: false,
                showOrderHistory: false,
                showOrderDetailsModal: false,
                isLoadingOrders: false,
                lastOrderNumber: '',
                lastOrderAmount: 0,
                lastOrderPaymentMethod: '',
                lastOrderId: '',
                selectedOrder: {},
                showCancelOrderModal: false,
                orderToCancel: null,
                isCancelling: false,
                showToast: false,
                toastMessage: '',
                isLoadingProducts: false,
                cashAmount: '',
                formattedCashAmount: '',


                
                init() {
                    // Set CSRF token for fetch requests
                    this.fetchCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    this.fetchTaxPercentage(); // Ambil nilai pajak saat inisialisasi
                    
                    // Add event listener for the custom event dispatched from the header dropdown
                    window.addEventListener('toggle-order-history', () => {
                        this.toggleOrderHistory();
                    });
                },
                // Numpad methods
                appendNumber(num) {
                    // Tambahkan angka ke cashAmount
                    this.cashAmount = (this.cashAmount || '') + num;
                    // Update formattedCashAmount
                    this.updateFormattedCashAmount();
                },
                
                clearCashAmount() {
                    this.cashAmount = '';
                    this.formattedCashAmount = '';
                },
                // Fungsi untuk format input cash
                formatCashInput() {
                    // Hapus semua karakter non-digit (termasuk titik)
                    const onlyNumbers = this.formattedCashAmount.replace(/\D/g, '');
                    
                    // Update nilai cashAmount
                    this.cashAmount = onlyNumbers;
                    
                    // Update nilai yang terformat
                    this.updateFormattedCashAmount();
                },
                // Update tampilan input dengan format ribuan
                updateFormattedCashAmount() {
                    // Format angka dengan pemisah ribuan
                    if (this.cashAmount) {
                        this.formattedCashAmount = Number(this.cashAmount).toLocaleString('id-ID');
                    } else {
                        this.formattedCashAmount = '';
                    }
                },
                // Fungsi baru untuk menangani input keyboard
                handleKeyDown(event) {
                    // Hanya menerima angka (0-9), backspace, delete, dan tombol arah
                    const allowedKeys = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight'];
                    
                    if (!allowedKeys.includes(event.key)) {
                        event.preventDefault();
                    }
                },
                // Fungsi untuk memvalidasi input cash
                validateCashInput() {
                    // Hapus karakter selain angka
                    this.cashAmount = this.cashAmount.replace(/[^\d]/g, '');
                },
                
                get cartTotal() {
                    return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
                },
                
                fetchTaxPercentage() {
                    this.isLoadingTax = true; // Aktifkan indikator loading
                    
                    fetch('{{ route('get.tax') }}')
                        .then(response => response.json())
                        .then(data => {
                            this.taxPercentage = data.tax_percentage ?? 0; // Set nilai pajak, default 0%
                        })
                        .catch(error => {
                            console.error('Error fetching tax:', error);
                        })
                        .finally(() => {
                            this.isLoadingTax = false; // Nonaktifkan indikator loading setelah selesai
                        });
                },

                
                get taxAmount() {
                    return (this.cartTotal * this.taxPercentage) / 100;
                },
                
                get finalAmount() {
                    return this.cartTotal + this.taxAmount - this.discountAmount;
                },
                
                formatCurrency(amount) {
                    const numberAmount = Number(amount);
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                    }).format(numberAmount);
                },
                
                formatDate(dateString) {
                    const options = { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    };
                    return new Date(dateString).toLocaleDateString('id-ID', options);
                },
                
                toggleOrderHistory() {
                    this.showOrderHistory = !this.showOrderHistory;
                    if (this.showOrderHistory && this.orders.length === 0) {
                        this.loadOrders();
                    }
                },
                
                loadOrders() {
                    this.isLoadingOrders = true;
                    
                    fetch('{{ route('orders.history') }}')
                        .then(response => response.json())
                        .then(data => {
                            this.orders = data;
                            this.filteredOrders = data;
                            this.isLoadingOrders = false;
                        })
                        .catch(error => {
                            console.error('Error loading orders:', error);
                            this.isLoadingOrders = false;
                        });
                },
                
                filterOrders() {
                    if (!this.orderSearchQuery) {
                        this.filteredOrders = this.orders;
                        return;
                    }
                    
                    const query = this.orderSearchQuery.toLowerCase();
                    this.filteredOrders = this.orders.filter(order => 
                        (order.order_number && order.order_number.toLowerCase().includes(query)) ||
                        (order.customer_name && order.customer_name.toLowerCase().includes(query))
                    );
                },
                
                viewOrderDetails(order) {
                    // Fetch complete order details including items
                    fetch(`{{ url('/orders') }}/${order.id}`)
                        .then(response => response.json())
                        .then(data => {
                            this.selectedOrder = data;
                            this.showOrderDetailsModal = true;
                        })
                        .catch(error => {
                            console.error('Error loading order details:', error);
                            alert('Failed to load order details');
                        });
                },
                
                printOrderReceipt(orderId) {
                    const receiptWindow = window.open(`{{ url('/pos/receipt') }}/${orderId}`, '_blank', 'width=400,height=600');
                    
                    if (receiptWindow) {
                        receiptWindow.addEventListener('load', function() {
                            receiptWindow.print();
                        });
                    }
                },
                
                showCancelConfirmation(order) {
                    this.orderToCancel = order;
                    this.showCancelOrderModal = true;
                },
                
                forceProductsRefresh() {
                    // Meminta data produk terbaru dari server
                    fetch('{{ route("pos.products.get") }}')
                        .then(response => response.json())
                        .then(data => {
                            // Perbarui array products dengan data terbaru
                            this.products = data;
                            // Perbarui filtered products
                            this.filterProducts();
                        })
                        .catch(error => {
                            console.error('Error refreshing products:', error);
                        });
                },
                
                cancelOrder() {
                    if (!this.orderToCancel || this.isCancelling) return;
                    
                    this.isCancelling = true;
                    
                    // Kirim request ke backend
                    fetch(`/pos/orders/${this.orderToCancel.id}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.isCancelling = false;
                        
                        if (data.success) {
                            // Cara 1: Perbarui produk satu per satu
                            if (data.updatedProducts && data.updatedProducts.length > 0) {
                                data.updatedProducts.forEach(updatedProduct => {
                                    // Temukan dan perbarui produk dalam array lokal
                                    const index = this.products.findIndex(p => p.id === updatedProduct.id);
                                    if (index !== -1) {
                                        // Perbarui seluruh objek produk
                                        this.products[index] = updatedProduct;
                                    }
                                });
                                
                                // Force re-render filtered products
                                this.filteredProducts = JSON.parse(JSON.stringify(
                                    this.products.filter(product => {
                                        const matchesCategory = !this.selectedCategory || 
                                            product.category_id == this.selectedCategory;
                                        const matchesSearch = !this.searchQuery || 
                                            product.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                                        return matchesCategory && matchesSearch && 
                                            product.is_available && product.stock > 0;
                                    })
                                ));
                            } else {
                                // Cara 2: Jika cara 1 tidak berhasil, refresh semua produk
                                this.forceProductsRefresh();
                            }
                            
                            // Update status order dalam daftar order
                            const orderIndex = this.orders.findIndex(o => o.id === this.orderToCancel.id);
                            if (orderIndex !== -1) {
                                this.orders[orderIndex].status = 'cancelled';
                                // Force update array untuk trigger render
                                this.filteredOrders = [...this.orders]; 
                            }
                            
                            // Jika sedang melihat detail order yang dibatalkan, perbarui statusnya
                            if (this.selectedOrder && this.selectedOrder.id === this.orderToCancel.id) {
                                this.selectedOrder = data.order;
                            }
                            
                            this.showCancelOrderModal = false;
                            this.showToast = true;
                            this.toastMessage = data.message;
                            
                            // Auto hide toast after 3 seconds
                            setTimeout(() => {
                                this.showToast = false;
                            }, 3000);
                            
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        this.isCancelling = false;
                        console.error('Error:', error);
                        alert('Terjadi kesalahan. Silakan coba lagi.');
                    });
                },
                
                filterProducts() {
                    this.filteredProducts = this.products.filter(product => {
                        const matchesCategory = !this.selectedCategory || product.category_id == this.selectedCategory;
                        const matchesSearch = !this.searchQuery || 
                            product.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                        
                        // Hanya tampilkan produk yang tersedia dan stoknya lebih dari 0
                        return matchesCategory && matchesSearch && 
                            product.is_available && product.stock > 0;
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
                            max_quantity: product.stock,
                            image: `/storage/${product.image_path}`

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
                // Clear cart
                clearCart() {
                    this.cart = [];
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
                    
                    // Add cash validation for payment method 1 (Cash)
                    if (this.paymentMethodId == 1) {
                        if (!this.cashAmount || Number(this.cashAmount) < this.finalAmount) {
                            alert('Cash amount must be equal to or greater than the total amount');
                            return;
                        }
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
                    
                    // Add cash details if payment method is cash
                    if (this.paymentMethodId == 1) {
                        orderData.cash_amount = Number(this.cashAmount);
                        orderData.cash_change = Number(this.cashAmount) - this.finalAmount;
                    }
                    
                    fetch('{{ route('pos.orders.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.fetchCsrfToken
                        },
                        body: JSON.stringify(orderData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.isProcessing = false;
                        
                        if (data.success) {
                            // Store the last order info for receipt
                            this.lastOrderId = data.order.id;
                            this.lastOrderNumber = data.order.order_number;
                            this.lastOrderAmount = data.order.final_amount;
                            this.lastOrderPaymentMethod = data.order.payment_method.name;
                            
                            // Show success modal
                            this.showSuccessModal = true;
                            
                            // Reset products stock in the UI
                            this.updateProductsStock(orderData.items);
                            
                            // Add new order to the history if it's loaded
                            if (this.orders.length > 0) {
                                this.orders.unshift(data.order);
                                this.filterOrders();
                            }
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
                    // Open the receipt in a new window using the route with the order ID
                    const receiptWindow = window.open(`{{ url('/pos/receipt') }}/${this.lastOrderId}`, '_blank', 'width=400,height=600');
                    
                    if (receiptWindow) {
                        // Let the browser load the page before printing
                        receiptWindow.addEventListener('load', function() {
                            receiptWindow.print();
                        });
                    }
                },
                
                closeSuccessModal() {
                    this.showSuccessModal = false;
                    // Clear cart and reset form
                    this.cart = [];
                    this.customerName = '';
                    this.paymentMethodId = '';
                    this.orderNotes = '';
                    this.cashAmount = '';
                    this.formattedCashAmount = '';
                }
            };
        }
    </script>
</body>
</html>