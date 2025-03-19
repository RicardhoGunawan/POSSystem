<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->decimal('tax_percentage', 5, 2)->default(0)->after('store_name');
        });
    }

    public function down()
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn('tax_percentage');
        });
    }
};
