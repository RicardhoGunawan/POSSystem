<!-- resources/views/livewire/pos-system.blade.php -->
<div class="flex flex-col h-full">
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 h-full">
        <!-- Products Section -->
        <div class="md:col-span-2 bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 bg-gray-50 border-b">
                <div class="flex flex-col md:flex-row gap-2">
                    <div class="flex-1">
                        <input 
                            type="text" 
                            wire:model.live="searchQuery" 
                            placeholder="Cari produk..." 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                    </div>
                    <div>
                        <select 
                            wire:model.live="selectedCategory" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 p-4 overflow-y-auto" style="max-height: 60vh;">
                @forelse($products as $product)
                    <div 
                        wire:click="addToCart({{ $product->id }})" 
                        class="bg-white border rounded-lg p-3 cursor-pointer hover:shadow-md transition-shadow"
                    >
                        @if($product->image_path)
                            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-md bg-gray-200 mb-2">
                                <img src="{{ $product->image_path }}" alt="{{ $product->name }}" class="h-full w-full object-cover object-center">
                            </div>
                        @else
                            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-md bg-gray-200 mb-2 flex items-center justify-center">
                                <span class="text-gray-400">No Image</span>
                            </div>
                        @endif
                        <h3 class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $product->category }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-900">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                    </div>
                @empty
                    <div class="col-span-full text-center p-8">
                        <p class="text-gray-500">Tidak ada produk ditemukan</p>
                    </div>
                @endforelse
            </div>
        </div>
        
        <!-- Cart Section -->
        <div class="bg-white rounded-lg shadow overflow-hidden flex flex-col">
            <div class="p-4 bg-gray-50 border-b">
                <h2 class="text-lg font-medium text-gray-900">Keranjang Belanja</h2>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4" style="max-height: 40vh;">
                @forelse($cart as $item)
                    <div class="border-b py-3">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium">{{ $item['name'] }}</h3>
                                <p class="text-sm text-gray-500">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                            </div>
                            <button wire:click="removeFromCart({{ $item['id'] }})" class="text-red-500 hover:text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-2 flex items-center">
                            <label class="text-sm text-gray-500 mr-2">Jumlah:</label>
                            <input 
                                type="number" 
                                wire:change="updateQuantity({{ $item['id'] }}, $event.target.value)" 
                                value="{{ $item['quantity'] }}" 
                                min="1" 
                                class="w-16 px-2 py-1 border border-gray-300 rounded-md text-center focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                            />
                        </div>
                        <div class="mt-2">
                            <label class="text-sm text-gray-500">Catatan:</label>
                            <input 
                                type="text" 
                                wire:change="updateNotes({{ $item['id'] }}, $event.target.value)" 
                                value="{{ $item['notes'] }}" 
                                class="w-full px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                            />
                        </div>
                        <div class="mt-2 text-right">
                            <span class="text-sm font-medium">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <p class="text-gray-500">Keranjang belanja kosong</p>
                    </div>
                @endforelse
            </div>
            
            <!-- Order Summary -->
            <div class="p-4 border-t">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Subtotal:</span>
                        <span class="text-sm font-medium">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Pajak ({{ $taxRate }}%):</span>
                        <span class="text-sm font-medium">Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Diskon (%):</span>
                        <input 
                            type="number" 
                            wire:change="updateDiscountRate($event.target.value)" 
                            value="{{ $discountRate }}" 
                            min="0" 
                            max="100" 
                            class="w-16 px-2 py-1 border border-gray-300 rounded-md text-center focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                        />
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Diskon:</span>
                        <span class="text-sm font-medium">Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t">
                        <span class="text-base font-medium">Total:</span>
                        <span class="text-base font-bold">Rp {{ number_format($finalAmount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Customer Information -->
            <div class="p-4 border-t">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Pelanggan</label>
                        <input 
                            type="text" 
                            wire:model="customerName" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                            placeholder="Nama pelanggan"
                        >
                        @error('customerName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Metode Pembayaran</label>
                        <select 
                            wire:model="selectedPaymentMethod" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">Pilih metode pembayaran</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedPaymentMethod') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Catatan</label>
                        <textarea 
                            wire:model="notes" 
                            rows="2" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                            placeholder="Catatan tambahan"
                        ></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="p-4 bg-gray-50 border-t">
                <div class="grid grid-cols-2 gap-3">
                    <button
                        wire:click="resetCart"
                        class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Reset
                    </button>
                    <button
                        wire:click="processOrder"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Proses Pesanan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>