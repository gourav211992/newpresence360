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
        Schema::create('erp_pi_so_mapping', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('so_id');
            $table->unsignedBigInteger('so_item_id');
            $table->unsignedBigInteger('bom_id')->nullable();
            $table->unsignedBigInteger('bom_detail_id')->nullable();
            $table->unsignedBigInteger('item_id');
            $table->string('item_code');
            $table->double('qty',[20,6])->default(0);
            $table->unsignedBigInteger('pi_id')->nullable();
            $table->unsignedBigInteger('pi_item_id')->nullable();
            $table->double('pi_item_qty',[20,6])->default(0);
            $table->json('attributes')->nullable();
            $table->foreign('so_id')->references('id')->on('erp_sale_orders')->onDelete('cascade');
            $table->foreign('so_item_id')->references('id')->on('erp_so_items')->onDelete('cascade');
            $table->foreign('bom_id')->references('id')->on('erp_boms')->onDelete('cascade');
            $table->foreign('bom_detail_id')->references('id')->on('erp_bom_details')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('erp_items')->onDelete('cascade');
            $table->foreign('pi_id')->references('id')->on('erp_purchase_indents')->onDelete('cascade');
            $table->foreign('pi_item_id')->references('id')->on('erp_pi_items')->onDelete('cascade');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_pi_so_mapping');
    }
};
