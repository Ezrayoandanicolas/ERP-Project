<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class ExpenseStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(
                'Pengeluaran Hari Ini',
                'Rp ' . number_format(
                    Expense::whereDate('tanggal', today())->sum('jumlah'),
                    0,
                    ',',
                    '.'
                )
            )
                ->icon('heroicon-o-banknotes')
                ->color('danger'),

            Stat::make(
                'Pengeluaran Bulan Ini',
                'Rp ' . number_format(
                    Expense::whereMonth('tanggal', now()->month)
                        ->whereYear('tanggal', now()->year)
                        ->sum('jumlah'),
                    0,
                    ',',
                    '.'
                )
            )
                ->icon('heroicon-o-calendar')
                ->color('warning'),

            Stat::make(
                'Total Pengeluaran',
                'Rp ' . number_format(
                    Expense::sum('jumlah'),
                    0,
                    ',',
                    '.'
                )
            )
                ->icon('heroicon-o-chart-bar')
                ->color('gray'),
        ];
    }
}
