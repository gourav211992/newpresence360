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
        Schema::create('erp_master_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id')->comment('id of erp_services');
            $table->foreign('service_id')->references('id')->on('erp_services');
            $table->enum('policy_level', ['G', 'C', 'O'])->default('O');
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE)->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('erp_organization_master_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('service_id')->comment('id of erp_services');
            $table->foreign('service_id')->references('id')->on('erp_services');
            $table->enum('policy_level', ['G', 'C', 'O'])->default('O');
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE)->index();
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
        Schema::dropIfExists('erp_master_policies');
        Schema::dropIfExists('erp_organization_master_policies');
    }
};
