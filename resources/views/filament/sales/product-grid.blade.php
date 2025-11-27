<div>
    @php
        $store = $this->data['store_id'] ?? null;
        $outlet = $this->data['outlet_id'] ?? null;

        $products = ($store && $outlet)
            ? \App\Models\Product::where('store_id', $store)->get()
            : [];
    @endphp

    @if(!$store)
        <p class="text-gray-500 text-sm">Pilih <strong>Toko Induk</strong> dulu.</p>

    @elseif(!$outlet)
        <p class="text-gray-500 text-sm">Pilih <strong>Outlet</strong> dulu.</p>

    @else
        <h3 class="font-bold text-lg mb-3">Pilih Produk</h3>

        <div class="grid grid-cols-4 gap-4">

            @foreach($products as $product)
                <div class="border p-3 rounded shadow bg-white flex flex-col items-center">

                    <div class="w-full aspect-square bg-gray-200 rounded overflow-hidden mb-2">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" class="w-full h-full object-cover">
                        @else
                            <div class="flex items-center justify-center h-full text-xs text-gray-500">FOTO</div>
                        @endif
                    </div>

                    <div class="text-sm font-semibold text-center mb-1">
                        {{ $product->name }}
                    </div>

                    <div class="text-xs text-gray-600 mb-2">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </div>

                    <button
                        wire:click="addItem({{ $product->id }})"
                        type="button"
                        class="px-3 py-1 bg-primary-600 text-white rounded text-xs"
                    >
                        Tambah
                    </button>

                </div>
            @endforeach

        </div>

        <hr class="my-6">

        {{-- CART SECTION --}}
        <h3 class="font-bold text-lg mb-3">Keranjang</h3>

        @php $cart = $this->cart ?? [] @endphp

        @if(count($cart) == 0)
            <p class="text-gray-500 text-sm">Belum ada produk.</p>
        @else
            <table class="w-full text-sm mb-4">
                <thead>
                    <tr class="text-left border-b">
                        <th class="py-1">Produk</th>
                        <th class="py-1">Qty</th>
                        <th class="py-1">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart as $id => $item)
                        <tr class="border-b">
                            <td class="py-2">{{ $item['name'] }}</td>
                            <td class="py-2">
                                x{{ $item['qty'] }}
                            </td>
                            <td class="py-2">
                                Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                            </td>
                            <td class="py-2 text-right">
                                <button
                                    wire:click="removeItem({{ $id }})"
                                    class="text-red-600 text-xs"
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- TOTAL & DISCOUNT --}}
        <div class="bg-gray-50 p-4 rounded">
            <div class="flex justify-between text-sm mb-2">
                <div>Total:</div>
                <div>Rp {{ number_format($this->total ?? 0, 0, ',', '.') }}</div>
            </div>

            <div class="flex justify-between text-sm mb-2">
                <div>Diskon:</div>
                <div>Rp {{ number_format($this->discount ?? 0, 0, ',', '.') }}</div>
            </div>

            <div class="flex justify-between font-bold text-base border-t pt-2">
                <div>Grand Total:</div>
                <div>
                    Rp {{ number_format(($this->total ?? 0) - ($this->discount ?? 0), 0, ',', '.') }}
                </div>
            </div>
        </div>

    @endif
</div>
