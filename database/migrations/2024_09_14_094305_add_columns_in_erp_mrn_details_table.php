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
        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->string('book_code')->nullable()->after('series_id');
            $table->string('vendor_code')->nullable()->after('vendor_id');
            $table->string('currency_code')->nullable()->after('currency_id');
            $table->unsignedBigInteger('payment_term_id')->nullable()->after('currency_code');
            $table->string('payment_term_code')->nullable()->after('payment_term_id');

            $table->foreign('payment_term_id')->references('id')->on('erp_payment_terms')->onDelete('cascade');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->string('book_code')->nullable()->after('series_id');
            $table->string('vendor_code')->nullable()->after('vendor_id');
            $table->string('currency_code')->nullable()->after('currency_id');
            $table->unsignedBigInteger('payment_term_id')->nullable()->after('currency_code');
            $table->string('payment_term_code')->nullable()->after('payment_term_id');

            $table->foreign('payment_term_id')->references('id')->on('erp_payment_terms')->onDelete('cascade');
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->unsignedBigInteger('hsn_id')->nullable()->after('item_name');
            $table->string('uom_code')->nullable()->after('uom_id');
            $table->string('inventory_uom_code')->nullable()->after('inventory_uom_id');
            $table->decimal('header_discount_amount', 15, 2)->default(0.00)->after('discount_amount'); 

            $table->foreign('hsn_id')->references('id')->on('erp_hsns')->onDelete('cascade');

        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('hsn_id')->nullable()->after('item_name');
            $table->string('uom_code')->nullable()->after('uom_id');
            $table->string('inventory_uom_code')->nullable()->after('inventory_uom_id');
            $table->decimal('header_discount_amount', 15, 2)->default(0.00)->after('discount_amount'); 
        
            $table->foreign('hsn_id')->references('id')->on('erp_hsns')->onDelete('cascade');
        });

        Schema::table('erp_mrn_attributes', function (Blueprint $table) {
            $table->string('item_code')->nullable()->after('item_id');
        });

        Schema::table('erp_mrn_attribute_histories', function (Blueprint $table) {
            $table->string('item_code')->nullable()->after('item_id');
        });

        Schema::table('erp_mrn_extra_amounts', function (Blueprint $table) {
            $table->unsignedBigInteger('ted_id')->nullable()->after('ted_level');
            $table->string('ted_name')->nullable()->after('ted_id');
        });

        Schema::table('erp_mrn_extra_amount_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('ted_id')->nullable()->after('ted_level');
            $table->string('ted_name')->nullable()->after('ted_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_extra_amount_histories', function (Blueprint $table) {
            $table->dropColumn('ted_id');
            $table->dropColumn('ted_name');
        });

        Schema::table('erp_mrn_extra_amounts', function (Blueprint $table) {
            $table->dropColumn('ted_id');
            $table->dropColumn('ted_name');
        });

        Schema::table('erp_mrn_attribute_histories', function (Blueprint $table) {
            $table->dropColumn('item_code');
        });

        Schema::table('erp_mrn_attributes', function (Blueprint $table) {
            $table->dropColumn('item_code');
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->dropForeign(['hsn_id']);
            $table->dropColumn('hsn_id');
            $table->dropColumn('uom_code');
            $table->dropColumn('inventory_uom_code');
            $table->dropColumn('header_discount_amount');
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->dropForeign(['hsn_id']);
            $table->dropColumn('hsn_id');
            $table->dropColumn('uom_code');
            $table->dropColumn('inventory_uom_code');
            $table->dropColumn('header_discount_amount');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->dropColumn('book_code');
            $table->dropColumn('vendor_code');
            $table->dropColumn('currency_code');
            $table->dropForeign(['payment_term_id']);
            $table->dropColumn('payment_term_id');
            $table->dropColumn('payment_term_code');
        });

        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->dropColumn('book_code');
            $table->dropColumn('vendor_code');
            $table->dropColumn('currency_code');
            $table->dropForeign(['payment_term_id']);
            $table->dropColumn('payment_term_id');
            $table->dropColumn('payment_term_code');
        });
    }
};
