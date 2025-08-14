<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('erp_stores', function (Blueprint $table) {
            $table->enum('store_location_type', ConstantHelper::ERP_STORE_LOCATION_TYPES)
            ->default(ConstantHelper::STOCKK);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_stores', function (Blueprint $table) {
            $table->dropColumn('store_location_type');
        });
    }
};
