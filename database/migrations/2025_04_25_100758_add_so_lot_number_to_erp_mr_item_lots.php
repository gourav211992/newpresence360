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
        Schema::table('erp_mr_item_lot_details', function (Blueprint $table) {
            //
            $table->string('so_lot_number')->nullable();
        });
        Schema::table('erp_sr_item_lot_details', function (Blueprint $table) {
            //
            $table->string('so_lot_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mr_item_lot_details', function (Blueprint $table) {
            //
            $table->dropColumn('so_lot_number');
        });
        Schema::table('erp_sr_item_lot_details', function (Blueprint $table) {
            //
            $table->dropColumn('so_lot_number');
        });
    }
};
