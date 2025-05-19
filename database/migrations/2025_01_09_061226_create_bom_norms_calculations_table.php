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
        Schema::create('erp_bom_norms_cals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bom_id')->comment('erp_boms id');
            $table->unsignedBigInteger('bom_detail_id')->comment('erp_bom_details id');
            $table->double('qty_per_unit',6)->nullable();
            $table->double('total_qty',6)->nullable();
            $table->double('std_qty',6)->nullable();
            $table->timestamps();
            $table->foreign('bom_id')->references('id')->on('erp_boms')->onDelete('cascade');
            $table->foreign('bom_detail_id')->references('id')->on('erp_bom_details')->onDelete('cascade');
        });
        Schema::create('erp_bom_norms_cals_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bom_id')->comment('erp_boms_history id');
            $table->unsignedBigInteger('bom_detail_id')->comment('erp_bom_details_history id');
            $table->double('qty_per_unit',6)->nullable();
            $table->double('total_qty',6)->nullable();
            $table->double('std_qty',6)->nullable();
            $table->timestamps();
            $table->foreign('bom_id')->references('id')->on('erp_boms_history')->onDelete('cascade');
            $table->foreign('bom_detail_id')->references('id')->on('erp_bom_details_history')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_bom_norms_cals');
        Schema::dropIfExists('erp_bom_norms_cals_history');
    }
};
