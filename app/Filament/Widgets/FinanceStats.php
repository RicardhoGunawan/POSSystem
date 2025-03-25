<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Expense;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class FinanceStats extends BaseWidget
{
    protected static ?int $sort = 1; // Menempatkan widget di paling atas

    protected function getColumns(): int
    {
        return 3; // Menambah kolom untuk persentase keuntungan
    }

    protected function getCards(): array
    {
        // Hitung total pendapatan (tidak termasuk pesanan yang dibatalkan)
        $totalIncome = Order::where('status', '!=', 'cancelled')->sum('final_amount');
        
        // Hitung total pengeluaran
        $totalExpense = Expense::sum('amount');

        // Hitung keuntungan bersih
        $netProfit = $totalIncome - $totalExpense;

        // Hitung persentase keuntungan
        $profitPercentage = $totalIncome > 0 
            ? (($netProfit / $totalIncome) * 100) 
            : 0;

        return [
            Card::make('Total Income', 'Rp ' . number_format($totalIncome, 0, ',', '.'))
                ->description('Pendapatan Kotor')
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),

            Card::make('Total Expenses', 'Rp ' . number_format($totalExpense, 0, ',', '.'))
                ->description('Total Pengeluaran')
                ->icon('heroicon-o-banknotes')
                ->color('danger'),

            Card::make('Net Profit', 'Rp ' . number_format($netProfit, 0, ',', '.'))
                ->description(number_format($profitPercentage, 2) . '% dari Income')
                ->icon('heroicon-o-chart-bar')
                ->color($netProfit >= 0 ? 'primary' : 'warning')
        ];
    }
}