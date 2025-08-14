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
        Schema::create('erp_wh_structures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
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
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->unsignedBigInteger('wh_structure_id')->nullable()->index();
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
            $table->tinyInteger('is_storage_point')->default(0)->index();
            $table->string('status', 191)->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_wh_storage_points', function (Blueprint $table) {
            $table->id();
            $table->string('name', 291)->nullable()->index();
            $table->unsignedBigInteger('wh_level_id')->nullable()->index();
            $table->unsignedBigInteger('wh_detail_id')->nullable()->index();
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
        Schema::dropIfExists('erp_wh_storage_points');
        Schema::dropIfExists('erp_wh_details');
        Schema::dropIfExists('erp_wh_levels');
        Schema::dropIfExists('erp_wh_structures');
    }
};
