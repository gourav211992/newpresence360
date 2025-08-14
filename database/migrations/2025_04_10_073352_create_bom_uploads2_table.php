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
        Schema::dropIfExists('erp_bom_uploads');
        Schema::create('erp_bom_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('bom');
            $table->string('customizable')->default('no');
            $table->string('bom_type')->default('fixed');
            $table->string('production_route_id')->nullable();
            $table->string('production_route_name')->nullable();
            $table->string('production_type')->default('In-house');
            $table->string('product_item_id')->nullable();
            $table->string('product_item_code')->nullable();
            $table->string('product_item_name')->nullable();
            $table->string('uom_id')->nullable();
            $table->string('uom_code')->nullable();
            $table->json('product_attributes')->nullable();
            $table->string('item_id')->nullable();    
            $table->string('item_code')->nullable();    
            $table->string('item_uom_id')->nullable();    
            $table->string('item_uom_code')->nullable();    
            $table->json('item_attributes')->nullable();  
            $table->string('attribute_name_1')->nullable();    
            $table->string('attribute_value_1')->nullable();    
            $table->string('attribute_name_2')->nullable();    
            $table->string('attribute_value_2')->nullable();    
            $table->string('attribute_name_3')->nullable();    
            $table->string('attribute_value_3')->nullable();    
            $table->string('attribute_name_4')->nullable();    
            $table->string('attribute_value_4')->nullable();    
            $table->string('attribute_name_5')->nullable();    
            $table->string('attribute_value_5')->nullable();    
            $table->string('consumption_qty')->nullable();    
            $table->string('cost_per_unit')->nullable();    
            $table->string('station_id')->nullable();    
            $table->string('station_name')->nullable();    
            $table->boolean('migrate_status')->default(false);    
            $table->string('bom_id')->nullable();    
            $table->json('reason')->nullable();    
            $table->timestamps();

            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_bom_uploads');
    }
};
