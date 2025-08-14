<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
            $table->decimal('current_value_after_dep', 15, 2)->after('current_value')->nullable();
            // Ensure that `original_value` column exists. Adjust if needed.
        });
        DB::statement('UPDATE erp_finance_fixed_asset_registration SET current_value_after_dep = current_value WHERE current_value IS NOT NULL');
    
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
            $table->dropColumn('current_value_after_dep');
        });
    }
};
