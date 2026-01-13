<x-filament-panels::page>
    <script src="https://cdn.tailwindcss.com"></script>

    @php
        $isAdminStore = auth()->user()->hasRole('admin_store');
        $isCashier = auth()->user()->hasRole('cashier');
    @endphp

    {{-- ==========================
        FORM ATAS
    =========================== --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        {{-- Toko Induk --}}
        <div>
            <label class="text-sm font-semibold mb-1 block">Toko Induk</label>
            <select wire:model.live="store_id"
                @if($isCashier || $isAdminStore) disabled @endif
                class="w-full border rounded-lg px-3 py-2 text-sm
                    bg-white dark:bg-gray-800
                    text-gray-800 dark:text-gray-100
                    border-gray-300 dark:border-gray-600
                    focus:ring-primary-500 focus:border-primary-500
                    disabled:opacity-60 disabled:cursor-not-allowed">
                <option value="">Pilih Toko Induk</option>
                @foreach(\App\Models\Store::orderBy('name')->get() as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Outlet --}}
        <div>
            <label class="text-sm font-semibold mb-1 block
                text-gray-700 dark:text-gray-300">
                Outlet
            </label>

            <select
                wire:model.live="outlet_id"
                @if($isCashier) disabled @endif
                class="w-full border rounded-lg px-3 py-2 text-sm
                    bg-white dark:bg-gray-800
                    text-gray-800 dark:text-gray-100
                    border-gray-300 dark:border-gray-600
                    focus:ring-primary-500 focus:border-primary-500
                    disabled:cursor-not-allowed disabled:opacity-60
                    {{ $store_id ? '' : 'opacity-40' }}"
                {{ $store_id ? '' : 'disabled' }}
            >

                <option value="" class="text-gray-500 dark:text-gray-400">
                    Pilih Outlet
                </option>

                @foreach(\App\Models\Outlet::where('store_id',$store_id)->orderBy('name')->get() as $o)
                    <option value="{{ $o->id }}">
                        {{ $o->name }}
                    </option>
                @endforeach

            </select>
        </div>


        {{-- Customer --}}
        <div>
            <label class="text-sm font-semibold mb-1 block
                text-gray-700 dark:text-gray-300">
                Customer
            </label>

            <select
                wire:model.live="customer_name"
                class="w-full border rounded-lg px-3 py-2 text-sm
                    bg-white dark:bg-gray-800
                    text-gray-800 dark:text-gray-100
                    border-gray-300 dark:border-gray-600
                    focus:ring-primary-500 focus:border-primary-500"
            >
                <option value="Umum" class="text-gray-500 dark:text-gray-400">
                    Umum
                </option>

                @foreach(
                    \App\Models\User::whereHas('roles', fn($q) => $q->where('name','member'))
                        ->orderBy('name')
                        ->get()
                as $m)
                    <option value="{{ $m->name }}">
                        {{ $m->name }}
                    </option>
                @endforeach
            </select>
        </div>


        {{-- Payment --}}
        <div>
            <label class="text-sm font-semibold mb-1 block
                text-gray-700 dark:text-gray-300">
                Metode Pembayaran
            </label>

            <select
                wire:model.live="payment_method"
                class="w-full border rounded-lg px-3 py-2 text-sm
                    bg-white dark:bg-gray-800
                    text-gray-800 dark:text-gray-100
                    border-gray-300 dark:border-gray-600
                    focus:ring-primary-500 focus:border-primary-500"
            >
                <option value="cash">Cash</option>
                <option value="qris">QRIS</option>
                <option value="transfer">Transfer</option>
                <option value="credit">Credit</option>
            </select>
        </div>


        {{-- Date --}}
        <div>
            <label class="text-sm font-semibold mb-1 block
                text-gray-700 dark:text-gray-300">
                Tanggal
            </label>

            <input
                type="date"
                wire:model.live="sale_date"
                class="w-full border rounded-lg px-3 py-2 text-sm
                    bg-white dark:bg-gray-800
                    text-gray-800 dark:text-gray-100
                    border-gray-300 dark:border-gray-600
                    focus:ring-primary-500 focus:border-primary-500"
            >
        </div>

    </div>

    {{-- ==========================
        MAIN GRID
    =========================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- ==========================
            PRODUCT GRID
        =========================== --}}
        <div class="lg:col-span-3">
            <div class="border rounded-xl p-5 shadow-sm
                bg-white dark:bg-gray-900
                border-gray-200 dark:border-gray-700">

                <h3 class="font-bold text-lg mb-4
                    text-gray-800 dark:text-gray-100">
                    Pilih Produk
                </h3>

                @if(!$store_id)
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Pilih <b>Toko Induk</b> dulu.
                    </p>

                @elseif(!$outlet_id)
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Pilih <b>Outlet</b> dulu.
                    </p>

                @else
                    {{-- KATEGORI --}}
                    <div class="flex gap-2 overflow-x-auto mb-4">
                        <button
                            wire:click="$set('category_id', null)"
                            class="px-4 py-2 text-xs rounded-full transition
                                {{ $category_id === null
                                    ? 'bg-primary-600 text-white'
                                    : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200'
                                }}">
                            Semua
                        </button>

                        @foreach(\App\Models\Category::where('store_id',$store_id)->orderBy('name')->get() as $cat)
                            <button
                                wire:click="$set('category_id', {{ $cat->id }})"
                                class="px-4 py-2 text-xs rounded-full transition
                                    {{ $category_id === $cat->id
                                        ? 'bg-primary-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200'
                                    }}">
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>

                    {{-- PRODUCT LIST --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">

                        @foreach($items as $item)
                            <div
                                wire:key="{{ $item['key'] }}"
                                class="border rounded-xl p-3 flex flex-col
                                    bg-white dark:bg-gray-800
                                    border-gray-200 dark:border-gray-700">

                                {{-- Image --}}
                                <div class="aspect-square rounded overflow-hidden
                                    bg-gray-100 dark:bg-gray-700">
                                    @if($item['image'])
                                        <img
                                            src="{{ asset('storage/'.$item['image']) }}"
                                            class="w-full h-full object-cover">
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="mt-2 text-center flex-grow">
                                    <p class="text-sm font-semibold
                                        text-gray-800 dark:text-gray-100">
                                        {{ $item['name'] }}
                                    </p>

                                    <p class="text-xs
                                        text-gray-500 dark:text-gray-400">
                                        Stok: {{ $item['stock'] }}
                                        {{ $item['type'] === 'variant'
                                            ? 'paket'
                                            : ($item['unit'] ?? 'pcs')
                                        }}
                                    </p>

                                    @if($item['type'] === 'variant')
                                        <p class="text-xs
                                            text-gray-400 dark:text-gray-500">
                                            {{ $item['pcs'] }} pcs
                                        </p>
                                    @endif

                                    <p class="text-xs
                                        text-gray-600 dark:text-gray-300">
                                        Rp {{ number_format($item['price'],0,',','.') }}
                                    </p>
                                </div>

                                {{-- Button --}}
                                <x-filament::button
                                    size="xs"
                                    color="primary"
                                    class="w-full mt-2"
                                    wire:click="addItem('{{ $item['type'] }}', {{ $item['id'] }})"
                                    wire:loading.attr="disabled"
                                    wire:target="addItem">
                                    Tambah
                                </x-filament::button>
                            </div>
                        @endforeach

                    </div>
                @endif
            </div>
        </div>


        {{-- ==========================
            CART
        =========================== --}}
        <div class="lg:col-span-2">
            <div class="border rounded-xl shadow-sm p-5
                bg-white dark:bg-gray-900
                border-gray-200 dark:border-gray-700">

                <h3 class="font-bold text-lg mb-4
                    text-gray-800 dark:text-gray-100">
                    Keranjang
                </h3>

                @forelse($cart as $item)
                
                    <div class="flex gap-3 mb-4 items-start">

                        {{-- IMAGE --}}
                        <div class="w-16 h-16 rounded overflow-hidden flex-shrink-0
                            bg-gray-100 dark:bg-gray-700">
                            @if(!empty($item['image']))
                                <img
                                    src="{{ asset('storage/'.$item['image']) }}"
                                    class="w-full h-full object-cover"
                                >
                            @endif
                        </div>

                        {{-- INFO --}}
                        <div class="flex-grow">
                            <p class="text-sm font-semibold
                                text-gray-800 dark:text-gray-100">
                                {{ $item['name'] }}
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    (@ Rp {{ number_format($item['price'],0,',','.') }})
                                </span>
                            </p>

                            <p class="text-xs font-semibold
                                text-primary-600 dark:text-primary-400">
                                Rp {{ number_format($item['subtotal'],0,',','.') }}
                            </p>

                            <div class="flex items-center gap-2 mt-2">

                                <x-filament::icon-button
                                    icon="heroicon-o-minus"
                                    size="sm"
                                    wire:click="updateQty('{{ $item['key'] }}', -1)" />

                                <input
                                    type="number"
                                    min="1"
                                    class="w-14 text-center border rounded text-sm
                                        bg-white dark:bg-gray-800
                                        text-gray-800 dark:text-gray-100
                                        border-gray-300 dark:border-gray-600"
                                    wire:model.lazy="cart.{{ $item['key'] }}.qty"
                                    wire:change="updateQtyManual('{{ $item['key'] }}', $event.target.value)">

                                <x-filament::icon-button
                                    icon="heroicon-o-plus"
                                    size="sm"
                                    wire:click="updateQty('{{ $item['key'] }}', 1)" />

                                <x-filament::button
                                    size="xs"
                                    color="danger"
                                    class="ml-auto"
                                    wire:click="removeFromCart('{{ $item['key'] }}')">
                                    Hapus
                                </x-filament::button>

                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm
                        text-gray-400 dark:text-gray-500">
                        Keranjang kosong.
                    </p>
                @endforelse
            </div>

            {{-- SUMMARY --}}
            <div class="border rounded-xl shadow-sm p-4 mt-2
                bg-white dark:bg-gray-900
                border-gray-200 dark:border-gray-700">

                <div class="flex justify-between mb-2
                    text-gray-700 dark:text-gray-300">
                    <span>Subtotal</span>
                    <span>
                        Rp {{ number_format(collect($cart)->sum('subtotal'),0,',','.') }}
                    </span>
                </div>

                <div class="flex justify-between mb-2 items-center
                    text-gray-700 dark:text-gray-300">
                    <span>Diskon</span>
                    <input
                        type="number"
                        wire:model.blur="discount"
                        class="w-24 text-right px-2 py-1 rounded border text-sm
                            bg-white dark:bg-gray-800
                            text-gray-800 dark:text-gray-100
                            border-gray-300 dark:border-gray-600">
                </div>

                <hr class="my-2 border-gray-200 dark:border-gray-700">

                <div class="flex justify-between font-bold text-lg
                    text-gray-800 dark:text-gray-100">
                    <span>Total</span>
                    <span>Rp {{ number_format($total,0,',','.') }}</span>
                </div>

                <x-filament::button
                    color="success"
                    size="lg"
                    class="w-full mt-3"
                    wire:click="save">
                    Checkout ({{ count($cart) }})
                </x-filament::button>

                @if($lastSaleId)
                    <x-filament::button
                        color="primary"
                        size="lg"
                        class="w-full mt-2"
                        wire:click="printLastSale">
                        Print Struk
                    </x-filament::button>
                @endif
            </div>
        </div>

    </div>
</x-filament-panels::page>
