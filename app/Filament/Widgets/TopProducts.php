<?php

namespace App\Filament\Widgets;

use App\Models\SaleItem;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class TopProducts extends BaseWidget
{
    protected static ?string $heading = 'Produk Terlaris Bulan Ini';

    protected int|string|array $columnSpan = 'full';

    public int $totalQty = 0;

    protected function getTableQuery(): Builder
    {
        $month = $this->filters['month'] ?? now()->month;
        $outlet = $this->filters['outlet'] ?? null;

        $baseQuery = SaleItem::query()
            ->whereMonth('created_at', $month)
            ->when($outlet, function ($q) use ($outlet) {
                $q->whereHas('sale', fn ($s) => $s->where('outlet_id', $outlet));
            });

        $this->totalQty = (clone $baseQuery)->sum('qty');

        return $baseQuery
            ->select([
                DB::raw('UUID() AS record_id'),
                'product_id',
                DB::raw('SUM(qty) AS total_qty'),
                DB::raw('SUM(qty * price) AS total_price'),
            ])
            ->with('product')
            ->groupBy('product_id')
            ->orderByRaw('SUM(qty) DESC');

    }


    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('month')
                ->label('Bulan')
                ->options(
                    collect(range(1, 12))->mapWithKeys(fn ($m) => [
                        $m => \Carbon\Carbon::create(null, $m)->translatedFormat('F')
                    ])
                )
                ->default(now()->month)
                ->query(function (Builder $query, array $data) {
                    if (! $data['value']) {
                        return $query;
                    }

                    return $query->whereMonth('created_at', $data['value']);
                }),

            Tables\Filters\SelectFilter::make('week')
            ->label('Minggu')
            ->options([
                1 => 'Minggu ke-1',
                2 => 'Minggu ke-2',
                3 => 'Minggu ke-3',
                4 => 'Minggu ke-4',
                5 => 'Minggu ke-5',
            ])
            ->query(function (Builder $query, array $data) {
                if (! $data['value']) {
                    return $query;
                }

                $month = $this->filters['month'] ?? now()->month;
                $year  = now()->year;

                // awal bulan
                $startOfMonth = Carbon::create($year, $month, 1);

                // hitung range minggu
                $start = $startOfMonth->copy()
                    ->addWeeks($data['value'] - 1)
                    ->startOfWeek();

                $end = $start->copy()->endOfWeek();

                return $query->whereBetween('created_at', [$start, $end]);
            }),


            Tables\Filters\SelectFilter::make('outlet')
                ->label('Outlet')
                ->options(\App\Models\Outlet::pluck('name', 'id'))
                ->query(function (Builder $query, array $data) {
                    if (! $data['value']) {
                        return $query;
                    }

                    return $query->whereHas('sale', fn ($q) => $q->where('outlet_id', $data['value']));
                }),
        ];
    }

    public function getTableRecordKey(Model $record): string
    {
        return $record->record_id;
    }

    protected function getTableColumns(): array
    {
        // total semua qty bulan ini untuk hitung persentase
        // $totalQty = SaleItem::whereMonth('created_at', now()->month)->sum('qty');

        return [
            Tables\Columns\TextColumn::make('product.name')
                ->label('Produk')
                ->sortable(false),

            Tables\Columns\TextColumn::make('total_qty')
                ->label('Terjual')
                ->sortable(),

            Tables\Columns\TextColumn::make('total_price')
                ->label('Total Penjualan')
                ->money('IDR', true)
                ->sortable(),

            Tables\Columns\TextColumn::make('contribution')
                ->label('Kontribusi')
                ->getStateUsing(function ($record) {
                    if ($this->totalQty <= 0) {
                        return '0%';
                    }

                    return number_format(
                        ($record->total_qty / $this->totalQty) * 100,
                        1
                    ) . '%';
                }),


        ];
    }

    protected function calculateTotalQty(): int
    {
        $filters = $this->getTable()->getFiltersForm()->getState();

        $query = SaleItem::query();

        if (!empty($filters['month'])) {
            $query->whereMonth('created_at', $filters['month']);
        }

        if (!empty($filters['outlet'])) {
            $query->whereHas('sale', fn ($q) =>
                $q->where('outlet_id', $filters['outlet'])
            );
        }

        if (!empty($filters['week'])) {
            $month = $filters['month'] ?? now()->month;
            $year  = now()->year;

            $start = Carbon::create($year, $month, 1)
                ->addWeeks($filters['week'] - 1)
                ->startOfWeek();

            $end = $start->copy()->endOfWeek();

            $query->whereBetween('created_at', [$start, $end]);
        }

        return $query->sum('qty');
    }

}
