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
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->string('lr_number', 25) -> nullable() -> after('vehicle_no');
        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->string('lr_number', 25) -> nullable() -> after('vehicle_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->dropColumn(['lr_number']);
        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->dropColumn(['lr_number']);
        });
    }
};
