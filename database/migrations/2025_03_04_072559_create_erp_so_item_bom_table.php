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
        Schema::create('erp_so_item_bom', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_order_id');
            $table->unsignedBigInteger('so_item_id');
            $table->unsignedBigInteger('bom_id');
            $table->unsignedBigInteger('bom_detail_id');
            $table->unsignedBigInteger('uom_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_code');
            $table->json('item_attributes')->nullable();
            $table->double('qty', 20, 6);
            $table->unsignedBigInteger('station_id');
            $table->string('station_name');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_so_item_bom_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('sale_order_id');
            $table->unsignedBigInteger('so_item_id');
            $table->unsignedBigInteger('bom_id');
            $table->unsignedBigInteger('bom_detail_id');
            $table->unsignedBigInteger('uom_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_code');
            $table->json('item_attributes')->nullable();
            $table->double('qty', 20, 6);
            $table->unsignedBigInteger('station_id');
            $table->string('station_name');
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
        Schema::dropIfExists('erp_so_item_bom');
    }
};
