<?php

use App\Helpers\ConstantHelper;
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
        Schema::create('erp_dpr_template_masters', function (Blueprint $table) {
            $table->id();

            $table->string('template_name')->nullable();
            $table->string('status')->default(ConstantHelper::ACTIVE)->nullable();
            // $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE)->nullable();

            $table->unsignedBigInteger('group_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('organization_id')->index();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_dpr_template_masters');
    }
};
