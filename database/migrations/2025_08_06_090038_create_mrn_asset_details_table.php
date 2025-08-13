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
        foreach (['erp_mrn_asset_details', 'erp_mrn_asset_details_history'] as $tableName) {
            $isHistory = str_contains($tableName, 'history');

            Schema::create($tableName, function (Blueprint $table) use ($isHistory) {
                $table->id();

                if ($isHistory) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }

                $table->unsignedBigInteger('header_id')->nullable();
                $table->json('detail_id')->nullable();
                $table->unsignedBigInteger('asset_category_id')->nullable();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('asset_code')->nullable();
                $table->string('asset_name')->nullable();
                $table->timestamp('capitalization_date');
                $table->bigInteger('estimated_life')->nullable();
                $table->decimal('salvage_value', 15, 2)->nullable();

                $table->timestamps();
                $table->softDeletes();

                // Foreign Keys
                if ($isHistory) {
                    $table->foreign('header_id')->references('id')->on('erp_mrn_header_histories')->onDelete('cascade');
                } else {
                    $table->foreign('header_id')->references('id')->on('erp_mrn_headers')->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_mrn_asset_details_history');
        Schema::dropIfExists('erp_mrn_asset_details');
    }
};
