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
            // Renaming columns
            // Adding new columns
            $table->decimal('header_discount', 15, 2)->nullable()->after('item_discount');
            $table->decimal('total_taxes', 15, 2)->nullable()->after('taxable_amount');
            $table->decimal('total_after_tax_amount', 15, 2)->nullable()->after('total_taxes');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            // Adding new columns
            $table->decimal('header_discount', 15, 2)->nullable()->after('item_discount');
            $table->decimal('total_taxes', 15, 2)->nullable()->after('taxable_amount');
            $table->decimal('total_after_tax_amount', 15, 2)->nullable()->after('total_taxes');
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            // Adding new column
            $table->decimal('header_discount_percentage', 15, 2)->nullable()->after('discount_amount');
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            // Adding new column
            $table->decimal('header_discount_percentage', 15, 2)->nullable()->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dropping the added columns in reverse migration
        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->dropColumn('header_discount_percentage');
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->dropColumn('header_discount_percentage');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->dropColumn('header_discount');
            $table->dropColumn('total_taxes');
            $table->dropColumn('total_after_tax_amount');
        });

        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->dropColumn('header_discount');
            $table->dropColumn('total_taxes');
            $table->dropColumn('total_after_tax_amount');
        });
    }
};
