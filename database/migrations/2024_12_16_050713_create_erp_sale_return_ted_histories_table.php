<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('erp_sale_return_ted_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('sale_return_id')->nullable();
            $table->unsignedBigInteger('return_item_id')->nullable();
            $table->enum('ted_type', ['Tax', 'Expense', 'Discount'])->comment('Tax, Expense, Discount');
            $table->enum('ted_level', ['H', 'D'])->comment('H or D');
            $table->unsignedBigInteger('ted_id')->nullable();
            $table->string('ted_group_code')->nullable();
            $table->string('ted_name')->nullable();
            $table->decimal('assessment_amount',15, 2)->default(0.00);
            $table->decimal('ted_percentage',15, 2)->default(0.00)->comment('TED Percentage');
            $table->decimal('ted_amount',15, 2)->default(0.00)->comment('TED Amount');
            $table->string('applicable_type')->nullable()->comment('Deduction, Collection');
            $table->foreign('sale_return_id')->references('id')->on('erp_sale_return_histories')->onDelete('cascade');
            $table->foreign('return_item_id')->references('id')->on('erp_sale_return_items_histories')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_sale_return_ted_histories');
    }
};
