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
        Schema::table('erp_customer_items', function (Blueprint $table) {
            $table->decimal('sell_price', 15, 4)->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->foreign('uom_id')->references('id')->on('erp_units')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_customer_items', function (Blueprint $table) {
            $table->dropForeign(['uom_id']);
            $table->dropColumn(['uom_id', 'sell_price']);
        });
    }
};
