<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('erp_finance_fixed_asset_sub', function (Blueprint $table) {
            $table->decimal('current_value_after_dep', 20, 2)->nullable()->after('current_value'); // adjust placement
            $table->decimal('total_depreciation', 20, 2)->default(0)->after('current_value_after_dep'); // adjust placement
        });

        Schema::table('erp_finance_fixed_asset_sub_history', function (Blueprint $table) {
            $table->decimal('current_value_after_dep', 20, 2)->nullable()->after('current_value'); // adjust placement
            $table->decimal('total_depreciation', 20, 2)->default(0)->after('current_value_after_dep'); // adjust placement
        
        });
        Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
            $table->integer('posted_days')->default(0)->after('days'); // adjust placement
        });
        Schema::table('erp_finance_fixed_asset_registration_history', function (Blueprint $table) {
            $table->integer('posted_days')->default(0)->after('days'); // adjust placement
        });
        DB::statement('UPDATE erp_finance_fixed_asset_sub SET current_value_after_dep = current_value');
        DB::statement('UPDATE erp_finance_fixed_asset_sub_history SET current_value_after_dep = current_value');
    
    }

    public function down(): void
    {
        Schema::table('erp_finance_fixed_asset_sub', function (Blueprint $table) {
            $table->dropColumn('current_value_after_dep');
            $table->dropColumn('total_depreciation');
        });

        Schema::table('erp_finance_fixed_asset_sub_history', function (Blueprint $table) {
            $table->dropColumn('current_value_after_dep');
            $table->dropColumn('total_depreciation');
        });
        Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
            $table->dropColumn('posted_days'); 
        });
        Schema::table('erp_finance_fixed_asset_registration_history', function (Blueprint $table) {
            $table->dropColumn('posted_days');
         });
    }
};
