<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            $table->dropColumn(['depreciation_percentage', 'depreciation_method']);
        });
    }

    public function down()
    {
        Schema::table('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            $table->decimal('depreciation_percentage', 10, 2)->nullable();
            $table->enum('depreciation_method', ['SLM', 'WDV'])->nullable();
        });
    }
};
