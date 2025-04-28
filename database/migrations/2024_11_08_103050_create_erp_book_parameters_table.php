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
        Schema::create('erp_book_parameters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('org_service_id')->comment('ID of erp_organization_services');
            $table->foreign('org_service_id')->references('id')->on('erp_organization_services');
            $table->unsignedBigInteger('service_param_id');
            $table->foreign('service_param_id')->references('id')->on('erp_service_parameters');
            $table->unsignedBigInteger('book_id');
            $table->foreign('book_id')->references('id')->on('erp_books');
            $table->string('parameter_name');
            $table->json('parameter_value');
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
        Schema::dropIfExists('erp_book_parameters');
    }
};
