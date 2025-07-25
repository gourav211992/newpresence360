<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            // Adding the new column after asset_category_id
            $table->string('prefix', 50)->after('asset_category_id');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    
    public function down()
    {
        Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            // Dropping the column if the migration is rolled back
            $table->dropColumn('prefix');
        });
    }
};
