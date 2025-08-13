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
        Schema::dropIfExists('erp_wh_structures');
        Schema::dropIfExists('erp_wh_levels');
        Schema::dropIfExists('erp_wh_details');
        Schema::dropIfExists('erp_wh_storage_point');
        Schema::dropIfExists('erp_wh_storage_points');

        Schema::create('erp_wh_structures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable()->index();
            $table->unsignedBigInteger('sub_store_id')->nullable()->index();
            $table->string('name', 291)->nullable()->index();
            $table->string('status', 191)->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_wh_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 291)->nullable()->index();
            $table->unsignedBigInteger('level')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->unsignedBigInteger('wh_structure_id')->nullable()->index();
            $table->unsignedBigInteger('store_id')->nullable()->index();
            $table->unsignedBigInteger('sub_store_id')->nullable()->index();
            $table->string('status', 191)->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_wh_details', function (Blueprint $table) {
            $table->id();
            $table->string('name', 291)->nullable()->index();
            $table->unsignedBigInteger('wh_level_id')->nullable()->index();
            $table->unsignedBigInteger('store_id')->nullable()->index();
            $table->unsignedBigInteger('sub_store_id')->nullable()->index();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->tinyInteger('is_storage_point')->default(0)->index();
            $table->decimal('max_weight', 15,2)->nullable()->index();
            $table->decimal('max_volume', 15,2)->nullable()->index();
            $table->decimal('current_weight', 15,2)->nullable();
            $table->decimal('current_volume', 15,2)->nullable();
            $table->string('status', 191)->nullable()->index();
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
        Schema::dropIfExists('erp_wh_details');
        Schema::dropIfExists('erp_wh_levels');
        Schema::dropIfExists('erp_wh_structures');
    }
};
