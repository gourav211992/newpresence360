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
        foreach (['erp_mrn_batch_details', 'erp_mrn_batch_details_history'] as $tableName) {
            $isHistory = str_contains($tableName, 'history');

            Schema::create($tableName, function (Blueprint $table) use ($isHistory) {
                $table->id();

                if ($isHistory) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }

                $table->unsignedBigInteger('header_id');
                $table->unsignedBigInteger('detail_id');
                $table->unsignedBigInteger('item_id');
                $table->string('batch_number')->nullable();
                $table->bigInteger('manufacturing_year')->nullable();
                $table->timestamp('expiry_date')->nullable();
                $table->decimal('quantity', 20,8)->nullable();
                $table->decimal('inventory_uom_qty', 20,8)->nullable();

                $table->timestamps();
                $table->softDeletes();

                // Foreign Keys
                if ($isHistory) {
                    $table->foreign('detail_id')->references('id')->on('erp_mrn_detail_histories')->onDelete('cascade');
                    $table->foreign('header_id')->references('id')->on('erp_mrn_header_histories')->onDelete('cascade');
                } else {
                    $table->foreign('detail_id')->references('id')->on('erp_mrn_details')->onDelete('cascade');
                    $table->foreign('header_id')->references('id')->on('erp_mrn_headers')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_mrn_batch_details_history');
        Schema::dropIfExists('erp_mrn_batch_details');
    }
};
