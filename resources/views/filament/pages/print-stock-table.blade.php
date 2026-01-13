<x-filament::page>
    <div class="flex gap-4 mb-4">
        <!-- FILTER TOKO -->
        <div class="w-48">
            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                Toko
            </label>
            <select
                wire:model.live="storeId"
                class="
                    w-full rounded-md
                    bg-white dark:bg-gray-800
                    border border-gray-300 dark:border-gray-600
                    text-sm text-gray-900 dark:text-gray-100
                    focus:ring-primary-500 focus:border-primary-500
                "
            >
                <option value="">Semua Toko</option>
                @foreach ($this->stores as $store)
                    <option value="{{ $store->id }}">
                        {{ $store->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- FILTER KATEGORI -->
        <div class="w-48">
            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                Kategori
            </label>
            <select
                wire:model.live="categoryId"
                class="
                    w-full rounded-md
                    bg-white dark:bg-gray-800
                    border border-gray-300 dark:border-gray-600
                    text-sm text-gray-900 dark:text-gray-100
                    focus:ring-primary-500 focus:border-primary-500
                "
            >
                <option value="">Semua Kategori</option>
                @foreach ($this->categories as $category)
                    <option value="{{ $category->id }}">
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>


    <x-filament::button
        class="mb-4"
        onclick="window.print()"
    >
        Print Tabel
    </x-filament::button>

    <!-- CONTAINER -->
    <div class="
        print-area
        rounded-xl
        p-4
        bg-white text-black
        dark:bg-gray-900 dark:text-gray-100
    ">

        <table class="mb-4 text-xs w-full border-collapse header-info">
            <tr>
                <td class="w-16 font-semibold border-0 p-1">Toko</td>
                <td class="w-2 border-0 p-1">:</td>
                <td class="w-64 border-0 p-1 uppercase">
                    {{ optional($this->stores->firstWhere('id', $storeId))->name ?? '____________________' }}
                </td>

                <td class="w-20 font-semibold border-0 p-1">Kategori</td>
                <td class="w-2 border-0 p-1">:</td>
                <td class="border-0 p-1 uppercase">
                    {{ optional($this->categories->firstWhere('id', $categoryId))->name ?? 'Semua Kategori' }}
                </td>
            </tr>

            <tr>
                <td class="font-semibold border-0 p-1">Tanggal</td>
                <td class="border-0 p-1">:</td>
                <td class="border-0 p-1">____________________</td>

                <td class="font-semibold border-0 p-1">Admin</td>
                <td class="border-0 p-1">:</td>
                <td class="border-0 p-1">____________________</td>
            </tr>
        </table>


        <table class="w-full border-collapse text-xs table-fixed border border-gray-400 dark:border-gray-600">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="border border-gray-400 dark:border-gray-600 p-1 w-8">No</th>
                    <th class="border border-gray-400 dark:border-gray-600 p-1 text-left col-nama" style="width: 260px;">Nama Produk</th>
                    <th class="border border-gray-400 dark:border-gray-600 p-1 w-20">Stock Masuk</th>
                    <th class="border border-gray-400 dark:border-gray-600 p-1 w-20">Stock Siang</th>
                    <th class="border border-gray-400 dark:border-gray-600 p-1 w-20">Stock Malam</th>
                    <th class="border border-gray-400 dark:border-gray-600 p-1 w-32 text-center" colspan="20">Penjualan</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($this->products as $i => $product)
                    <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                        <td class="border border-gray-400 dark:border-gray-600 p-1 text-center">
                            {{ $i + 1 }}
                        </td>
                        <td class="border border-gray-400 dark:border-gray-600 p-1 col-nama">
                            {{ $product->name }}
                        </td>
                        <td class="border border-gray-400 dark:border-gray-600 p-1"></td>
                        <td class="border border-gray-400 dark:border-gray-600 p-1"></td>
                        <td class="border border-gray-400 dark:border-gray-600 p-1"></td>
                        @for ($d = 1; $d <= 20; $d++)
                            <td class="border border-gray-400 dark:border-gray-600 p-1 penjualan-col"></td>
                        @endfor
                    </tr>
                @endforeach
                @for ($n = 1; $n <= 5; $n++)
                    <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                        <td class="border border-gray-400 dark:border-gray-600 p-1 text-center">
                            &nbsp;
                        </td>
                        <td class="border border-gray-400 dark:border-gray-600 p-1 col-nama">
                            
                        </td>
                        <td class="border border-gray-400 dark:border-gray-600 p-1"></td>
                        <td class="border border-gray-400 dark:border-gray-600 p-1"></td>
                        <td class="border border-gray-400 dark:border-gray-600 p-1"></td>
                        @for ($d = 1; $d <= 20; $d++)
                            <td class="border border-gray-400 dark:border-gray-600 p-1 penjualan-col"></td>
                        @endfor
                    </tr>
                @endfor
                <tr>
                    <td
                        colspan="25"
                        class="
                            border border-gray-400 dark:border-gray-600
                            p-2
                            align-top
                        "
                        style="height: 80px;"
                    >
                        <div class="text-xs font-semibold mb-1">
                            Catatan:
                        </div>

                        <div
                            class="
                                w-full
                                h-full
                                border border-gray-300 dark:border-gray-600
                                rounded
                                p-2
                                bg-white dark:bg-gray-800
                            "
                            contenteditable="true"
                        ></div>
                    </td>
                </tr>

            </tbody>

        </table>
    </div>

    <style>
        /* ======================
        MODE NORMAL (SCREEN)
        ====================== */
        .uppercase {
            text-transform: uppercase;
        }


        .print-area,
        .print-area table,
        .print-area th,
        .print-area td {
            font-size: 12px;
        }

        /* DARK MODE (FILAMENT) */
        .dark .print-area {
            background-color: #111827; /* gray-900 */
            color: #f9fafb;
        }

        .dark .print-area table th,
        .dark .print-area table td {
            border-color: #4b5563; /* gray-600 */
        }

        /* ======================
        PRINT MODE (FORCE LIGHT)
        ====================== */
        @media print {
            body * {
                visibility: hidden !important;
            }

            .print-area,
            .print-area * {
                visibility: visible !important;
            }

            .print-area {
                position: absolute;
                inset: 0;
                background: #ffffff !important;
                color: #000000 !important;
            }

            table th,
            table td {
                border: 1px solid #000 !important;
                background: #fff !important;
                color: #000 !important;
                font-size: 12px !important;
            }

            .penjualan-col {
                width: 24px;
                min-width: 24px;
                max-width: 24px;
                text-align: center;
            }

            .col-nama {
                width: auto !important;
                white-space: normal;
                word-break: break-word;
            }

            button,
            select,
            label {
                display: none !important;
            }
            .print-area div[contenteditable] {
                border: 1px solid #000 !important;
                min-height: 80px;
            }
        }

        .row-kosong td {
            height: 28px;          /* tinggi baris */
            min-height: 28px;
            vertical-align: middle;
        }

        .line {
            display: inline-block;
            min-width: 180px;
            border-bottom: 1px solid currentColor;
        }
        
        @media print {
            .header-info,
            .header-info tr,
            .header-info td {
                border: none !important;
                outline: none !important;
                box-shadow: none !important;
            }
        }


        </style>


    </style>

</x-filament::page>
