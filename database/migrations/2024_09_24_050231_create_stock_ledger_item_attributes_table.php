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
        Schema::create('stock_ledger_item_attributes', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('stock_ledger_id')->index(); 
            $table->unsignedBigInteger('item_id')->index(); 
            $table->string('item_code', 150)->nullable();
            $table->unsignedBigInteger('item_attribute_id')->index(); 
            $table->string('attribute_name', 199)->nullable();
            $table->string('attribute_value', 199)->nullable();
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE)->index();
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
        Schema::dropIfExists('stock_ledger_item_attributes');
    }
};
