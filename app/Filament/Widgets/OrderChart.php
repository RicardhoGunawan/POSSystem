<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class OrderChart extends ApexChartWidget
{
    protected static ?int $sort = 3; // Menempatkan widget ini di paling atas

    protected static ?string $chartId = 'ordersChart';
    protected static ?string $heading = 'Penjualan';

    // Tambahkan filter untuk memilih tampilan
    protected function getFilters(): ?array
    {
        return [
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
        ];
    }

    protected function getOptions(): array
    {
        $filter = $this->filter ?? 'daily'; // Default tampilan harian
        $dates = [];
        $salesData = [];

        if ($filter === 'daily') {
            $startDate = Carbon::now()->subDays(6);
            for ($i = 0; $i < 7; $i++) {
                $date = $startDate->copy()->addDays($i)->format('Y-m-d');
                $dates[] = Carbon::parse($date)->format('d M');
                $sales = Order::whereDate('created_at', $date)
                    ->where('status', '!=', 'cancelled')
                    ->sum('final_amount');
                $salesData[] = (int) $sales; // Pastikan nilai selalu integer
            }
        } elseif ($filter === 'weekly') {
            $startDate = Carbon::now()->subWeeks(6);
            for ($i = 0; $i < 7; $i++) {
                $week = $startDate->copy()->addWeeks($i)->format('W');
                $dates[] = "Minggu " . ($i + 1);
                $sales = Order::whereRaw('WEEK(created_at, 1) = ?', [$week])
                    ->where('status', '!=', 'cancelled')
                    ->sum('final_amount');
                $salesData[] = (int) $sales;
            }
        } elseif ($filter === 'monthly') {
            $startDate = Carbon::now()->subMonths(6);
            for ($i = 0; $i < 7; $i++) {
                $month = $startDate->copy()->addMonths($i)->format('Y-m');
                $dates[] = Carbon::parse($month)->format('M Y');
                $sales = Order::whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$month])
                    ->where('status', '!=', 'cancelled')
                    ->sum('final_amount');
                $salesData[] = (int) $sales;
            }
        }

        // Debugging
        \Log::info('Filter:', [$filter]);
        \Log::info('Dates:', $dates);
        \Log::info('Sales Data:', $salesData);

        return json_decode(json_encode([
            'chart' => [
                'type' => 'line',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Penjualan (Rp)',
                    'data' => array_values($salesData),
                ]
            ],
            'xaxis' => [
                'categories' => array_values($dates),
            ],
            'colors' => ['#10b981'],
            'tooltip' => [
                'y' => [
                    'formatter' => fn($value) => 'Rp ' . number_format($value, 0, ',', '.'),
                ],
            ],
        ]), true);
    }
}
