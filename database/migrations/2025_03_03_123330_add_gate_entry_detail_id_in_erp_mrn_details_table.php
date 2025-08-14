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
        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->bigInteger('gate_entry_detail_id')->after('purchase_order_item_id')->nullable();
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->bigInteger('gate_entry_detail_id')->after('purchase_order_item_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->dropColumn('gate_entry_detail_id');
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->dropColumn('gate_entry_detail_id');
        });
    }
};
