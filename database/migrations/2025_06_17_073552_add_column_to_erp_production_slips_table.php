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
        Schema::table('erp_production_slips', function (Blueprint $table) {
            $table->unsignedBigInteger('fg_sub_store_id')->nullable()->after('sub_store_id');
            $table->unsignedBigInteger('rg_sub_store_id')->nullable()->after('fg_sub_store_id');
        });
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->double('accepted_qty', 20, 6)->default(0)->after('qty');
            $table->double('subprime_qty', 20, 6)->default(0)->after('accepted_qty');
            $table->double('rejected_qty', 20, 6)->default(0)->after('subprime_qty');

        });
        Schema::table('erp_pslip_item_locations', function (Blueprint $table) {
            $table->double('accepted_qty', 20, 6)->default(0)->after('quantity');
            $table->double('subprime_qty', 20, 6)->default(0)->after('accepted_qty');
            $table->double('rejected_qty', 20, 6)->default(0)->after('subprime_qty');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pslip_item_locations', function (Blueprint $table) {
            $table->dropColumn('accepted_qty');
            $table->dropColumn('subprime_qty');
            $table->dropColumn('rejected_qty');
        });
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->dropColumn('accepted_qty');
            $table->dropColumn('subprime_qty');
            $table->dropColumn('rejected_qty');
        });
        Schema::table('erp_production_slips', function (Blueprint $table) {
            $table->dropColumn('fg_sub_store_id');
            $table->dropColumn('rg_sub_store_id');
        });
    }
};
