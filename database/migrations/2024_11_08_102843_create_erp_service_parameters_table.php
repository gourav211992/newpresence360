<?php

use App\Helpers\ServiceParametersHelper;
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
        Schema::create('erp_service_parameters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id')->comment('ID of erp_services');
            $table->foreign('service_id')->references('id')->on('erp_services');
            $table->string('name');
            $table->json('applicable_values');
            $table->json('default_value');
            $table->enum('type', ServiceParametersHelper::PARAMETER_TYPES) -> default(ServiceParametersHelper::COMMON_PARAMETERS);
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
        Schema::dropIfExists('erp_service_parameters');
    }
};
