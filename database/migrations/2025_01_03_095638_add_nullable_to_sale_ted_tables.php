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
        Schema::table('erp_sale_order_ted', function (Blueprint $table) {
            $table->double('ted_percentage', 15, 8) -> change() -> nullable();
        });
        Schema::table('erp_sale_order_ted_history', function (Blueprint $table) {
            $table->double('ted_percentage', 15, 8) -> change() -> nullable();
        });
        Schema::table('erp_sale_invoice_ted', function (Blueprint $table) {
            $table->double('ted_percentage', 15, 8) -> change() -> nullable();
        });
        Schema::table('erp_sale_invoice_ted_history', function (Blueprint $table) {
            $table->double('ted_percentage', 15, 8) -> change() -> nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_sale_order_ted', function (Blueprint $table) {
            $table->double('ted_percentage', 15, 2) -> change() -> nullable(false);
        });
        Schema::table('erp_sale_order_ted_history', function (Blueprint $table) {
            $table->double('ted_percentage', 15, 2) -> change() -> nullable(false);
        });
        Schema::table('erp_sale_invoice_ted', function (Blueprint $table) {
            $table->double('ted_percentage', 15, 2) -> change() -> nullable(false);
        });
        Schema::table('erp_sale_invoice_ted_history', function (Blueprint $table) {
            $table->double('ted_percentage', 15, 2) -> change() -> nullable(false);
        });
    }
};
