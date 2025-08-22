<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('erp_finance_fixed_asset_sub', function (Blueprint $table) {
            $table->decimal('current_value_after_dep', 30,15)->change();
            $table->decimal('total_depreciation', 30,15)->change(); // Change to DECIMAL(20,5)
        });
    }

    public function down()
    {
        Schema::table('erp_finance_fixed_asset_sub', function (Blueprint $table) {
            $table->decimal('current_value_after_dep', 20, 2)->change(); 
            $table->decimal('total_depreciation', 20, 2)->change(); // Revert back to DECIMAL(20,2)
        });
    }
};
