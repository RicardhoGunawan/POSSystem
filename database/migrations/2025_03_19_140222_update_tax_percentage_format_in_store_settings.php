<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Backup current values and update them (multiply by 100)
        $storeSettings = DB::table('store_settings')->get();
        
        foreach ($storeSettings as $setting) {
            DB::table('store_settings')
                ->where('id', $setting->id)
                ->update([
                    // Convert decimal percentage (e.g., 0.05) to whole number percentage (e.g., 5)
                    'tax_percentage' => $setting->tax_percentage * 100
                ]);
        }
        
        // Change column type to maintain the new format
        Schema::table('store_settings', function (Blueprint $table) {
            // First modify to decimal with enough precision to avoid data loss
            $table->decimal('tax_percentage', 8, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Back up current values and revert them (divide by 100)
        $storeSettings = DB::table('store_settings')->get();
        
        foreach ($storeSettings as $setting) {
            DB::table('store_settings')
                ->where('id', $setting->id)
                ->update([
                    // Convert whole number percentage back to decimal percentage
                    'tax_percentage' => $setting->tax_percentage / 100
                ]);
        }
        
        // Change column type back to original
        Schema::table('store_settings', function (Blueprint $table) {
            $table->decimal('tax_percentage', 5, 2)->change();
        });
    }
};