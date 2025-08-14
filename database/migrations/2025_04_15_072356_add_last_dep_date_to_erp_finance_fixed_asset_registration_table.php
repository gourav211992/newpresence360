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
            $table->date('last_dep_date')->nullable()->after('capitalize_date'); // replace 'your_reference_column' with the correct column name
    
        });
        DB::statement('UPDATE erp_finance_fixed_asset_registration SET last_dep_date = capitalize_date WHERE capitalize_date IS NOT NULL');
    
    }

    
    public function down()
    {
        Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
            $table->dropColumn('last_dep_date');
        });
    }
    
};
