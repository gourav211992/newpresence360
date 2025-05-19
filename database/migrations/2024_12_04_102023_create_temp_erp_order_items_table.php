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
        Schema::create('temp_erp_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_header_id')->nullable();    
            $table->string('order_item_id')->nullable();    
            $table->string('order_number')->nullable();    
            $table->string('item_code')->nullable();  
            $table->string('item_name')->nullable();  
            $table->double('rate')->nullable(); 
            $table->string('size')->nullable(); 
            $table->string('store_type')->nullable(); 
            $table->string('uom')->nullable();  
            $table->date('delivery_date')->nullable();      
            $table->double('total_order_value')->nullable();      
            $table->integer('order_quantity')->nullable();  
            $table->integer('delivered_quantity')->nullable();    
            $table->string('order_status')->nullable();
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
        Schema::dropIfExists('temp_erp_order_items');
    }
};
