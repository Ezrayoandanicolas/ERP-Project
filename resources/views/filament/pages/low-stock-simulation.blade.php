<x-filament::page>

    {{-- FILTER --}}
    <div class="flex gap-4 mb-4">

        <!-- OUTLET -->
        <div class="w-48">
            <label class="text-xs font-semibold
                text-gray-700 dark:text-gray-300">
                Outlet
            </label>

            <select
                wire:model.live="outletId"
                class="
                    w-full rounded-md
                    bg-white dark:bg-gray-800
                    border border-gray-300 dark:border-gray-600
                    text-sm text-gray-900 dark:text-gray-100
                    focus:ring-2 focus:ring-primary-500
                    focus:border-primary-500
                "
            >
                <option value="">Semua Outlet</option>
                @foreach ($this->getOutlets() as $outlet)
                    <option value="{{ $outlet->id }}">
                        {{ $outlet->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- STOK MINIMUM -->
        <div class="w-40">
            <label class="text-xs font-semibold
                text-gray-700 dark:text-gray-300">
                Stok Minimum
            </label>

            <input
                type="number"
                wire:model.live="minStock"
                class="
                    w-full rounded-md
                    bg-white dark:bg-gray-800
                    border border-gray-300 dark:border-gray-600
                    text-sm text-gray-900 dark:text-gray-100
                    focus:ring-2 focus:ring-primary-500
                    focus:border-primary-500
                "
            />
        </div>

    </div>


    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th class="border p-2 text-left">Produk</th>
                    <th class="border p-2 text-center">Stok</th>
                    <th class="border p-2 text-left">Kategori</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->getProducts() as $product)
                    <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                        <td class="border p-2">{{ $product->name }}</td>
                        <td class="border p-2 text-center text-red-600 font-bold">
                            {{ $product->stock }}
                        </td>
                        <td class="border p-2">
                            {{ $product->category->name ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="border p-4 text-center text-gray-500">
                            Tidak ada produk dengan stok rendah
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-filament::page>
