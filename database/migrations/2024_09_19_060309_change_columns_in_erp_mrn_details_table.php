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
            // Update existing columns with new precision and nullable constraint
            $table->decimal('rate', 15, 2)->nullable()->change();
            $table->decimal('basic_value', 15, 2)->nullable()->change(); 
            $table->decimal('discount_percentage', 10, 2)->nullable()->change(); 
            $table->decimal('discount_amount', 15, 2)->nullable()->change(); 
            $table->decimal('sgst_percentage', 10, 2)->nullable()->change(); 
            $table->decimal('cgst_percentage', 10, 2)->nullable()->change(); 
            $table->decimal('igst_percentage', 10, 2)->nullable()->change(); 
            $table->decimal('tax_value', 15, 2)->nullable()->change(); 
            $table->decimal('taxable_amount', 15, 2)->nullable()->change(); 
            $table->decimal('header_exp_amount', 15, 2)->nullable()->change(); 
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            // Update existing columns with new precision and nullable constraint
            $table->decimal('rate', 15, 2)->nullable()->change();
            $table->decimal('basic_value', 15, 2)->nullable()->change(); 
            $table->decimal('discount_percentage', 10, 2)->nullable()->change(); 
            $table->decimal('discount_amount', 15, 2)->nullable()->change(); 
            $table->decimal('sgst_percentage', 10, 2)->nullable()->change(); 
            $table->decimal('cgst_percentage', 10, 2)->nullable()->change(); 
            $table->decimal('igst_percentage', 10, 2)->nullable()->change(); 
            $table->decimal('tax_value', 15, 2)->nullable()->change(); 
            $table->decimal('taxable_amount', 15, 2)->nullable()->change(); 
            $table->decimal('header_exp_amount', 15, 2)->nullable()->change(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_details', function (Blueprint $table) {
            // Reverse the changes made in up()
            $table->decimal('rate', 15, 2)->nullable()->change();
            $table->decimal('basic_value', 15, 2)->nullable()->change(); 
            $table->decimal('discount_percentage', 10, 2)->nullable()->change(); 
            $table->decimal('discount_amount', 15, 2)->nullable()->change(); 
            $table->decimal('sgst_percentage', 10, 2)->nullable()->change(); 
            $table->decimal('cgst_percentage', 10, 2)->nullable()->change(); 
            $table->decimal('igst_percentage', 10, 2)->nullable()->change(); 
            $table->decimal('tax_value', 15, 2)->nullable()->change(); 
            $table->decimal('taxable_amount', 15, 2)->nullable()->change(); 
            $table->decimal('header_exp_amount', 15, 2)->nullable()->change(); 
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            // Reverse the changes made in up()
            $table->decimal('rate', 15, 2)->nullable()->change();
            $table->decimal('basic_value', 15, 2)->nullable()->change(); 
            $table->decimal('discount_percentage', 10, 2)->nullable()->change(); 
            $table->decimal('discount_amount', 15, 2)->nullable()->change(); 
            $table->decimal('sgst_percentage', 10, 2)->nullable()->change(); 
            $table->decimal('cgst_percentage', 10, 2)->nullable()->change(); 
            $table->decimal('igst_percentage', 10, 2)->nullable()->change(); 
            $table->decimal('tax_value', 15, 2)->nullable()->change(); 
            $table->decimal('taxable_amount', 15, 2)->nullable()->change(); 
            $table->decimal('header_exp_amount', 15, 2)->nullable()->change(); 
        });
    }
};
