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
        Schema::create('erp_sale_order_mrn_teds', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Tax', 'Expense', 'Discount']);
            $table->enum('level', ['Header', 'Detail']);
            $table->unsignedBigInteger('sale_order_id')->nullable();
            $table->unsignedBigInteger('sale_order_item_id')->nullable();
            $table->unsignedBigInteger('series_code')->nullable();
            $table->string('document_no')->nullable();
            $table->string('ted_code')->nullable();
            $table->double('assessment_amount', 10, 2);
            $table->double('ted_percentage', 10, 2);
            $table->double('ted_amount', 10, 2);
            $table->enum('applicability_type', ['Deduction', 'Collection']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_sale_order_mrn_teds');
    }
};
