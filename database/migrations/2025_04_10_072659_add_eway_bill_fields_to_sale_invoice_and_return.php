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
            $table->unsignedBigInteger('eway_bill_master_id')->nullable()->after('eway_bill_no');
            $table->string('transportation_mode',191)->nullable()->after('eway_bill_master_id');
            $table->tinyInteger('is_ewb_generated')->default(0)->after('e_invoice_status');
        });

        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->unsignedBigInteger('eway_bill_master_id')->nullable()->after('eway_bill_no');
            $table->string('transportation_mode',191)->nullable()->after('eway_bill_master_id');
            $table->tinyInteger('is_ewb_generated')->default(0)->after('e_invoice_status');
        });

        Schema::table('erp_sale_returns', function (Blueprint $table) {
            $table->unsignedBigInteger('eway_bill_master_id')->nullable()->after('eway_bill_no');
            $table->string('transportation_mode',191)->nullable()->after('eway_bill_master_id');
            $table->tinyInteger('is_ewb_generated')->default(0)->after('e_invoice_status');
        });

        Schema::table('erp_sale_return_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('eway_bill_master_id')->nullable()->after('eway_bill_no');
            $table->string('transportation_mode',191)->nullable()->after('eway_bill_master_id');
            $table->tinyInteger('is_ewb_generated')->default(0)->after('e_invoice_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->dropColumn(['eway_bill_master_id', 'transportation_mode', 'is_ewb_generated']);
        });

        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->dropColumn(['eway_bill_master_id', 'transportation_mode', 'is_ewb_generated']);
        });

        Schema::table('erp_sale_returns', function (Blueprint $table) {
            $table->dropColumn(['eway_bill_master_id', 'transportation_mode', 'is_ewb_generated']);
        });

        Schema::table('erp_sale_return_histories', function (Blueprint $table) {
            $table->dropColumn(['eway_bill_master_id', 'transportation_mode', 'is_ewb_generated']);
        });
    }
};
