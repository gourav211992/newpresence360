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
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_item_id')->nullable()->after('item_id');
            $table->string('customer_item_code', 100)->nullable()->after('customer_item_id');
            $table->string('customer_item_name', 200)->nullable()->after('customer_item_code');
        });
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_item_id')->nullable()->after('item_id');
            $table->string('customer_item_code', 100)->nullable()->after('customer_item_id');
            $table->string('customer_item_name', 200)->nullable()->after('customer_item_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['customer_item_id', 'customer_item_code', 'customer_item_name']);
        });
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            $table->dropColumn(['customer_item_id', 'customer_item_code', 'customer_item_name']);
        });
    }
};
