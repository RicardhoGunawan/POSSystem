<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\DB;

class ExpenseChart extends ApexChartWidget
{
    protected static ?int $sort = 2; // Menempatkan widget ini di paling atas

    protected static ?string $chartId = 'expensePieChart';
    protected static ?string $heading = 'Distribusi Pengeluaran';

    protected function getOptions(): array
    {
        // Ambil data pengeluaran berdasarkan kategori
        $expenses = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Ambil kategori dan jumlah pengeluaran
        $categories = $expenses->pluck('category')->toArray();
        $amounts = $expenses->pluck('total')->map(fn ($value) => (int) $value)->toArray();

        return json_decode(json_encode([
            'chart' => [
                'type' => 'pie',
                'height' => 300,
            ],
            'series' => $amounts,
            'labels' => $categories,
            'colors' => ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff', '#f56954'],
            'tooltip' => [
                'y' => [
                    'formatter' => "function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }",
                ],
            ],
        ]), true);
    }
}
