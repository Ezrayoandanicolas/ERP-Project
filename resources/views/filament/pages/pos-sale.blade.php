<x-filament-panels::page>
<script src="https://cdn.tailwindcss.com"></script>

    {{-- ==========================
        FORM ATAS
    =========================== --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        {{-- Toko Induk --}}
        <div>
            <label class="text-sm font-semibold mb-1 block">Toko Induk</label>
            <select
                wire:model.live="store_id"
                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-primary-600"
            >
                <option value="">Pilih Toko Induk</option>
                @foreach(\App\Models\Store::orderBy('name')->get() as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Outlet --}}
        <div>
            <label class="text-sm font-semibold mb-1 block">Outlet</label>
            <select
                wire:model.live="outlet_id"
                class="w-full border rounded-lg px-3 py-2 text-sm {{ $store_id ? '' : 'opacity-40' }}"
                {{ $store_id ? '' : 'disabled' }}
            >
                <option value="">Pilih Outlet</option>

                @foreach(\App\Models\Outlet::where('store_id',$store_id)->orderBy('name')->get() as $o)
                    <option value="{{ $o->id }}">{{ $o->name }}</option>
                @endforeach

            </select>
        </div>

        {{-- Payment --}}
        <div>
            <label class="text-sm font-semibold mb-1 block">Metode Pembayaran</label>
            <select
                wire:model.live="payment_method"
                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-primary-600"
            >
                <option value="cash">Cash</option>
                <option value="qris">QRIS</option>
                <option value="transfer">Transfer</option>
                <option value="credit">Credit / Utang</option>
            </select>
        </div>

    </div>



    {{-- ==========================
        PRODUK GRID
    =========================== --}}
    <div class="border rounded-xl p-5 bg-white shadow-sm mb-8">

        <h3 class="font-bold text-lg mb-4">Pilih Produk</h3>

        @if(!$store_id)
            <p class="text-gray-500 text-sm">Pilih <strong>Toko Induk</strong> dulu.</p>

        @elseif(!$outlet_id)
            <p class="text-gray-500 text-sm">Pilih <strong>Outlet</strong> dulu.</p>

        @else
            @php
                $products = \App\Models\Product::where('store_id',$store_id)->orderBy('name')->get();
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($products as $product)

                    <div class="product-card border rounded-xl bg-white p-3 transition cursor-pointer">

                        <div class="product-image">
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" class="w-full h-full object-cover">
                            @else
                                <div class="flex justify-center items-center h-full text-xs text-gray-400">No Image</div>
                            @endif
                        </div>

                        <p class="mt-2 text-sm font-semibold text-center h-10 leading-tight">
                            {{ $product->name }}
                        </p>

                        <p class="text-xs text-gray-600 text-center mb-2">
                            Rp {{ number_format($product->price,0,',','.') }}
                        </p>

                        <x-filament::button
                            color="primary"
                            size="xs"
                            wire:click="addToCart({{ $product->id }})"
                            class="w-full mt-2"
                        >
                            Tambah
                        </x-filament::button>


                    </div>

                @endforeach
            </div>

        @endif

    </div>



    {{-- ==========================
        CART & SUMMARY
    =========================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- CART --}}
        <div class="lg:col-span-2">
            <div class="border rounded-xl bg-white shadow-sm p-5">

                <h3 class="font-bold text-lg mb-4">Keranjang</h3>

                @if(empty($cart))
                    <p class="text-gray-400 text-sm">Belum ada item.</p>

                @else
                    @foreach($cart as $id => $item)

                        <div class="cart-row">

                            {{-- Checkbox --}}
                            <input type="checkbox" checked class="mt-1">

                            {{-- Product Image --}}
                            <div class="w-20 h-20 rounded-lg overflow-hidden bg-gray-100">
                                @php
                                    $img = \App\Models\Product::find($item['product_id'])->image_url ?? null;
                                @endphp

                                @if($img)
                                    <img src="{{ $img }}" class="w-full h-full object-cover">
                                @else
                                    <div class="flex justify-center items-center h-full text-gray-400 text-xs">No Image</div>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="flex-grow">

                                <p class="font-semibold text-sm">{{ $item['name'] }}</p>

                                <p class="text-primary-600 font-bold mt-1">
                                    Rp {{ number_format($item['price'],0,',','.') }}
                                </p>

                                <div class="flex items-center gap-3 mt-2">

                                    <x-filament::icon-button
                                        icon="heroicon-o-minus"
                                        color="secondary"
                                        size="sm"
                                        wire:click="updateQty({{ $id }}, -1)"
                                    />


                                    <span class="font-semibold text-sm">{{ $item['qty'] }}</span>

                                    <x-filament::icon-button
                                        icon="heroicon-o-plus"
                                        color="primary"
                                        size="sm"
                                        wire:click="updateQty({{ $id }}, 1)"
                                    />


                                    <x-filament::button
                                        wire:click="removeFromCart({{ $id }})"
                                        color="danger"
                                        size="xs"
                                        class="ml-auto"
                                    >
                                        Hapus
                                    </x-filament::button>


                                </div>

                            </div>

                            <div class="text-right font-semibold text-sm w-24">
                                Rp {{ number_format($item['subtotal'],0,',','.') }}
                            </div>

                        </div>

                    @endforeach
                @endif

            </div>
        </div>



        {{-- SUMMARY --}}
        <div>
            <div class="summary-card summary-sticky">

                <h3 class="font-bold text-lg mb-4">Ringkasan Pembelian</h3>

                <div class="flex justify-between text-sm mb-2">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format(collect($cart)->sum('subtotal'),0,',','.') }}</span>
                </div>

                <div class="flex justify-between text-sm mb-3">
                    <span>Diskon</span>
                    <input
                        type="number"
                        wire:model.blur="discount"
                        class="fi-input w-24 border rounded text-right px-2 py-1"
                    >
                </div>

                <hr class="my-3">

                <div class="flex justify-between text-base font-semibold mb-2 text-gray-700">
                    <span>Grand Total</span>
                    <span>
                        Rp {{ number_format(collect($cart)->sum('subtotal') - $discount, 0, ',', '.') }}
                    </span>
                </div>

                <div class="flex justify-between font-bold text-lg mb-4">
                    <span>Total</span>
                    <span>Rp {{ number_format($total,0,',','.') }}</span>
                </div>

               <x-filament::button
                    wire:click="save"
                    color="success"
                    size="lg"
                    class="w-full mt-3"
                >
                    Checkout ({{ count($cart) }})
                </x-filament::button>

                @if($lastSaleId)
                    <x-filament::button
                        wire:click="printLastSale"
                        color="primary"
                        size="lg"
                        class="w-full mt-3"
                    >
                        Print Struk
                    </x-filament::button>
                @endif



            </div>
        </div>

    </div>
</x-filament-panels::page>
