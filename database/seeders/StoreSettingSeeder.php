<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StoreSetting;


class StoreSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        StoreSetting::create([
            'store_name' => 'CodingSalatiga',
            'address_line_1' => 'Kota Salatiga',
            'address_line_2' => '-',
            'phone' => '(123) 456-7890',
            'footer_message' => 'Thank you for Comming!',
        ]);
    }
}
