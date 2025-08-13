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
       
        Schema::create('erp_logistics_mp_pricing', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('source_state_id')->nullable();
            $table->unsignedBigInteger('source_city_id')->nullable();
            $table->unsignedBigInteger('free_point')->nullable();
            $table->decimal('amount', 10, 2)->default(0);

            $table->unsignedBigInteger('customer_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_logistics_multi_point_pricing');
    }
};
