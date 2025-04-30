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
            // Adding new column 'total_lot_qty' to the 'erp_mr_item_lot_details' table
            $table->double('total_lot_qty', 20, 6)->after('lot_qty')->default(0)->index();
        });
        Schema::table('erp_sr_item_lot_details', function (Blueprint $table) {
            //
            // Adding new column 'total_lot_qty' to the 'erp_sr_item_lot_details' table
            $table->double('total_lot_qty', 20, 6)->after('lot_qty')->default(0)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_sr_item_lot_details', function (Blueprint $table) {
            //
            $table->dropColumn('total_lot_qty');
        });
        Schema::table('erp_mr_item_lot_details', function (Blueprint $table) {
            //
            $table->dropColumn('total_lot_qty');
        });
    }
};
