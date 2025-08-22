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
        Schema::table('erp_logistics_lorry_receipt', function (Blueprint $table) {
            $table->unsignedBigInteger('vehicle_id')->nullable()->after('consignee_id');
        });

        Schema::table('erp_logistics_lorry_receipt_history', function (Blueprint $table) {
            $table->unsignedBigInteger('vehicle_id')->nullable()->after('consignee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_logistics_lorry_receipt', function (Blueprint $table) {
            $table->dropColumn('vehicle_id');
        });

        Schema::table('erp_logistics_lorry_receipt_history', function (Blueprint $table) {
            $table->dropColumn('vehicle_id');
        });
    }
};
