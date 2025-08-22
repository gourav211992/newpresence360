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
        Schema::create('erp_psv_items_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('psv_header_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_code');
            $table->string('item_name')->nullable();
            $table->unsignedBigInteger('uom_id');
            $table->string('uom_code');
            $table->decimal('confirmed_qty', 15, 2)->default(0.00)->comment('Quantity confirmed during stock verification');
            $table->decimal('unconfirmed_qty', 15, 2)->default(0.00)->comment('Quantity unconfirmed during stock verification');
            $table->decimal('verified_qty', 15, 2)->default(0.00)->comment('Final verified quantity after stock verification');

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
        Schema::dropIfExists('erp_psv_items_history');
    }
};
