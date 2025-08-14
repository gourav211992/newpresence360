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
        Schema::table('erp_so_items', function (Blueprint $table) {
            $table->unsignedBigInteger('order_quotation_id')->nullable()->after('remarks');
            $table->double('quotation_order_qty',15, 2)->default(0.00)->after('order_quotation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_so_items', function (Blueprint $table) {
            //
        });
    }
};
