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
            Schema::create('erp_payment_terms', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable()->index();
                $table->string('alias')->nullable();
                $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE);
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
        Schema::dropIfExists('erp_payment_terms');
    }
};
