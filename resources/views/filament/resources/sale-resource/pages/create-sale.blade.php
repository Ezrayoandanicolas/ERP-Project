<x-filament-panels::page>

    {{-- FORM ASLI FILAMENT --}}
    <div>
        {{ $this->form }}
    </div>

    <hr class="my-6">

    {{-- PRODUCT GRID --}}
    @include('filament.sales.product-grid')

</x-filament-panels::page>
