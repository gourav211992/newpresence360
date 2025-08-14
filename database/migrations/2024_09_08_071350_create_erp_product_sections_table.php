<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
  
    public function up(): void
    {
        Schema::create('erp_product_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_product_sections');
    }
};
