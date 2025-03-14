<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Expense;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expenses = [
            ['title' => 'Pembelian Bahan Baku', 'description' => 'Pembelian bahan baku untuk produksi.', 'amount' => 500000, 'category' => 'Operasional', 'expense_date' => Carbon::now()->subDays(10), 'receipt_image' => null],
            ['title' => 'Biaya Listrik', 'description' => 'Pembayaran tagihan listrik bulan ini.', 'amount' => 750000, 'category' => 'Tagihan', 'expense_date' => Carbon::now()->subDays(5), 'receipt_image' => null],
            ['title' => 'Sewa Gedung', 'description' => 'Pembayaran sewa gedung selama 1 bulan.', 'amount' => 3000000, 'category' => 'Sewa', 'expense_date' => Carbon::now()->subDays(15), 'receipt_image' => null],
            ['title' => 'Gaji Karyawan', 'description' => 'Pembayaran gaji karyawan bulan ini.', 'amount' => 10000000, 'category' => 'Gaji', 'expense_date' => Carbon::now()->subDays(3), 'receipt_image' => null],
            ['title' => 'Perawatan Peralatan', 'description' => 'Servis dan perawatan peralatan kantor.', 'amount' => 1200000, 'category' => 'Perawatan', 'expense_date' => Carbon::now()->subDays(7), 'receipt_image' => null],
            ['title' => 'Pembelian Alat Tulis', 'description' => 'Pembelian alat tulis untuk kantor.', 'amount' => 250000, 'category' => 'Perlengkapan', 'expense_date' => Carbon::now()->subDays(2), 'receipt_image' => null],
            ['title' => 'Biaya Internet', 'description' => 'Tagihan internet bulanan.', 'amount' => 600000, 'category' => 'Tagihan', 'expense_date' => Carbon::now()->subDays(6), 'receipt_image' => null],
            ['title' => 'Transportasi', 'description' => 'Biaya transportasi untuk pengiriman barang.', 'amount' => 400000, 'category' => 'Operasional', 'expense_date' => Carbon::now()->subDays(4), 'receipt_image' => null],
            ['title' => 'Biaya Iklan', 'description' => 'Pembayaran iklan digital.', 'amount' => 2000000, 'category' => 'Pemasaran', 'expense_date' => Carbon::now()->subDays(8), 'receipt_image' => null],
            ['title' => 'Konsumsi Karyawan', 'description' => 'Biaya konsumsi untuk rapat dan acara kantor.', 'amount' => 500000, 'category' => 'Operasional', 'expense_date' => Carbon::now()->subDays(1), 'receipt_image' => null],
        ];

        foreach ($expenses as $expense) {
            Expense::create($expense);
        }
    }
}
