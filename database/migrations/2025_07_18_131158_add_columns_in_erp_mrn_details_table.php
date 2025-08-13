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
            $table->bigInteger('accepted_inv_uom_id')->nullable()->after('inventory_uom_qty');
            $table->string('accepted_inv_uom_code')->nullable()->after('accepted_inv_uom_id');
            $table->double('accepted_inv_uom_qty', 15, 6)->default(0)->after('accepted_inv_uom_code');
            $table->bigInteger('rejected_inv_uom_id')->nullable()->after('accepted_inv_uom_qty');
            $table->string('rejected_inv_uom_code')->nullable()->after('rejected_inv_uom_id');
            $table->double('rejected_inv_uom_qty', 15, 6)->default(0)->after('rejected_inv_uom_code');
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->string('inventory_uom_code')->nullable()->after('inventory_uom_id');
            $table->double('inventory_uom_qty', 15, 6)->default(0)->after('inventory_uom_code');
            $table->bigInteger('accepted_inv_uom_id')->nullable()->after('inventory_uom_qty');
            $table->string('accepted_inv_uom_code')->nullable()->after('accepted_inv_uom_id');
            $table->double('accepted_inv_uom_qty', 15, 6)->default(0)->after('accepted_inv_uom_code');
            $table->bigInteger('rejected_inv_uom_id')->nullable()->after('accepted_inv_uom_qty');
            $table->string('rejected_inv_uom_code')->nullable()->after('rejected_inv_uom_id');
            $table->double('rejected_inv_uom_qty', 15, 6)->default(0)->after('rejected_inv_uom_code');
        });

        Schema::table('erp_insp_details', function (Blueprint $table) {
            $table->bigInteger('accepted_inv_uom_id')->nullable()->after('inventory_uom_qty');
            $table->string('accepted_inv_uom_code')->nullable()->after('accepted_inv_uom_id');
            $table->double('accepted_inv_uom_qty', 15, 6)->default(0)->after('accepted_inv_uom_code');
            $table->bigInteger('rejected_inv_uom_id')->nullable()->after('accepted_inv_uom_qty');
            $table->string('rejected_inv_uom_code')->nullable()->after('rejected_inv_uom_id');
            $table->double('rejected_inv_uom_qty', 15, 6)->default(0)->after('rejected_inv_uom_code');
        });

        Schema::table('erp_insp_details_history', function (Blueprint $table) {
            $table->bigInteger('accepted_inv_uom_id')->nullable()->after('inventory_uom_qty');
            $table->string('accepted_inv_uom_code')->nullable()->after('accepted_inv_uom_id');
            $table->double('accepted_inv_uom_qty', 15, 6)->default(0)->after('accepted_inv_uom_code');
            $table->bigInteger('rejected_inv_uom_id')->nullable()->after('accepted_inv_uom_qty');
            $table->string('rejected_inv_uom_code')->nullable()->after('rejected_inv_uom_id');
            $table->double('rejected_inv_uom_qty', 15, 6)->default(0)->after('rejected_inv_uom_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_insp_details_history', function (Blueprint $table) {
            $table->dropColumn(
                [
                    'accepted_inv_uom_id', 
                    'accepted_inv_uom_code',
                    'accepted_inv_uom_qty', 
                    'rejected_inv_uom_id',
                    'rejected_inv_uom_code', 
                    'rejected_inv_uom_qty',
                ]
            );
        });

        Schema::table('erp_insp_details', function (Blueprint $table) {
            $table->dropColumn(
                [
                    'accepted_inv_uom_id', 
                    'accepted_inv_uom_code',
                    'accepted_inv_uom_qty', 
                    'rejected_inv_uom_id',
                    'rejected_inv_uom_code', 
                    'rejected_inv_uom_qty',
                ]
            );
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->dropColumn(
                [
                    'inventory_uom_code',
                    'inventory_uom_qty',
                    'accepted_inv_uom_id', 
                    'accepted_inv_uom_code',
                    'accepted_inv_uom_qty', 
                    'rejected_inv_uom_id',
                    'rejected_inv_uom_code', 
                    'rejected_inv_uom_qty',
                ]
            );
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->dropColumn(
                [
                    'accepted_inv_uom_id', 
                    'accepted_inv_uom_code',
                    'accepted_inv_uom_qty', 
                    'rejected_inv_uom_id',
                    'rejected_inv_uom_code', 
                    'rejected_inv_uom_qty',
                ]
            );
        });
    }
};
