<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErpHsnTaxPatternsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('erp_hsn_tax_patterns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hsn_id')->nullable()->index(); 
            $table->decimal('from_price', 10, 2)->nullable(); 
            $table->decimal('upto_price', 10, 2)->nullable(); 
            $table->unsignedBigInteger('tax_group_id')->nullable()->index(); 
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('hsn_id')->references('id') ->on('erp_hsns') ->onDelete('cascade'); 
            $table->foreign('tax_group_id')->references('id')->on('erp_taxes')->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_hsn_tax_patterns');
    }
}
