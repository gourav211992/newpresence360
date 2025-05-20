<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// use App\Helpers\ConstantHelper;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        # Manufactoring Item
        foreach(['erp_pwo_bom_mapping', 'erp_pwo_bom_mapping_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('pwo_id')->nullable();
                $table->unsignedBigInteger('pwo_mapping_id')->nullable();
                $table->unsignedBigInteger('bom_id')->nullable();
                $table->unsignedBigInteger('bom_detail_id')->nullable();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('item_code')->nullable();
                $table->json('attributes')->nullable();
                $table->unsignedBigInteger('uom_id')->nullable();
                $table->double('qty',[20,6])->default(0);
                $table->timestamps();
                if (str_contains($tbl, 'history')) {
                    $table->foreign('pwo_id')->references('id')->on('erp_mfg_orders_history')->onDelete('cascade');
                    // $table->foreign('pwo_mapping_id')->references('id')->on('erp_mo_products_history')->onDelete('cascade');
                    $table->foreign('bom_id')->references('id')->on('erp_boms_history')->onDelete('cascade');
                    $table->foreign('bom_detail_id')->references('id')->on('erp_bom_details_history')->onDelete('cascade');
                } else {
                    $table->foreign('pwo_id')->references('id')->on('erp_production_work_orders')->onDelete('cascade');
                    // $table->foreign('pwo_mapping_id')->references('id')->on('erp_mo_products')->onDelete('cascade');
                    $table->foreign('bom_id')->references('id')->on('erp_boms')->onDelete('cascade');
                    $table->foreign('bom_detail_id')->references('id')->on('erp_bom_details')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_pwo_bom_mapping_history');
        Schema::dropIfExists('erp_pwo_bom_mapping');
    }
};
