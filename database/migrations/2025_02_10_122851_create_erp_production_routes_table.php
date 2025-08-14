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
        Schema::create('erp_production_routes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();

            $table->string('name', 291)->nullable();
            $table->longText('description')->nullable();
            $table->string('status', 191)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_production_levels', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('production_route_id')->nullable();
            $table->unsignedBigInteger('level')->nullable();
            $table->string('name', 291)->nullable();
            $table->string('status', 191)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_pr_parent_details', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('production_route_id')->nullable();
            $table->unsignedBigInteger('production_level_id')->nullable();
            $table->unsignedBigInteger('level')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('status', 191)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_pr_details', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('production_route_id')->nullable();
            $table->unsignedBigInteger('production_level_id')->nullable();
            $table->unsignedBigInteger('pr_parent_id')->nullable();
            $table->unsignedBigInteger('level')->nullable();
            $table->unsignedBigInteger('station_id')->nullable();
            $table->enum('consumption', ['yes', 'no'])->default('no');
            $table->string('status', 191)->nullable();
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
        Schema::dropIfExists('erp_pr_details');
        Schema::dropIfExists('erp_pr_parent_details');
        Schema::dropIfExists('erp_production_levels');
        Schema::dropIfExists('erp_production_routes');
    }
};
