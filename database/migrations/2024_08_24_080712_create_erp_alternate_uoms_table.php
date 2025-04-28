<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;
class CreateErpAlternateUomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_alternate_uoms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id')->nullable()->index(); 
            $table->unsignedBigInteger('uom_id')->nullable()->index(); 
            $table->decimal('conversion_to_inventory', 10, 2)->nullable();
            $table->boolean('is_selling')->default(false)->nullable(); 
            $table->boolean('is_purchasing')->default(false)->nullable(); 
            $table->foreign('item_id')->references('id')->on('erp_items')->onDelete('cascade')->nullable(); // Made nullable
            $table->foreign('uom_id')->references('id')->on('erp_units')->onDelete('cascade')->nullable(); // Made nullable
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_alternate_uoms');
    }
}
