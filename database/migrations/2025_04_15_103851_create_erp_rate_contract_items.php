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
        Schema::create('erp_rate_contract_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rate_contract_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_code');
            $table->string('item_name')->nullable();
            $table->unsignedBigInteger('hsn_id');
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('uom_id');
            $table->string('uom_code');
            $table->decimal('moq', 15, 2)->default(0.00);//minimum order quantity
            $table->decimal('from_qty', 15, 2)->default(0.00);
            $table->decimal('to_qty', 15, 2)->default(0.00);
            $table->decimal('rate', 15, 2)->default(0.00);
            $table->unsignedBigInteger('lead_time')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('currency_code')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_rate_contract_items');
    }
};
