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
        Schema::create('erp_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('value')->nullable(); 
            $table->unsignedBigInteger('attribute_group_id')->nullable()->index(); 
            $table->foreign('attribute_group_id')->references('id')->on('erp_attribute_groups')->onDelete('cascade')->index();
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_attributes');
    }
};
