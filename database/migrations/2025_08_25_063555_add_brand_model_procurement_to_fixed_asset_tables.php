<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
            $table->string('brand_name')->nullable()->after('asset_code');
            $table->string('model_no')->nullable()->after('brand_name');
            $table->string('procurement_type')->nullable()->after('model_no');
        });

        Schema::table('erp_finance_fixed_asset_registration_history', function (Blueprint $table) {
            $table->string('brand_name')->nullable()->after('asset_code');
            $table->string('model_no')->nullable()->after('brand_name');
            $table->string('procurement_type')->nullable()->after('model_no');
        });
    }

    public function down(): void
    {
        Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
            $table->dropColumn(['brand_name', 'model_no', 'procurement_type']);
        });

        Schema::table('erp_finance_fixed_asset_registration_history', function (Blueprint $table) {
            $table->dropColumn(['brand_name', 'model_no', 'procurement_type']);
        });
    }
};
