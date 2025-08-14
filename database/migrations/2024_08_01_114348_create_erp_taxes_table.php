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
        Schema::create('erp_taxes', function (Blueprint $table) {
            $table->id();
            $table->string('tax_group')->nullable()->index();
            $table->text('description')->nullable();
            $table->enum('applicability_type', ConstantHelper::TAX_APPLICATION_TYPE)->default(ConstantHelper::COLLECTION);
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
        Schema::dropIfExists('erp_taxes');
    }
};
