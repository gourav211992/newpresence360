<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('erp_purchase_return_details', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('uom_code');
            $table->unsignedBigInteger('store_code')->nullable()->after('store_id');
        });

        Schema::table('erp_purchase_return_details_history', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('uom_code');
            $table->unsignedBigInteger('store_code')->nullable()->after('store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_purchase_return_details_history', function (Blueprint $table) {
            $table->dropColumn(['store_id', 'store_code']);
        });

        Schema::table('erp_purchase_return_details', function (Blueprint $table) {
            $table->dropColumn(['store_id', 'store_code']);
        });
    }
};
