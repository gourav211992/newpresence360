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
        Schema::create('erp_freight_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();

            $table->unsignedBigInteger('source_state_id');
            $table->unsignedBigInteger('source_city_id');
            $table->unsignedBigInteger('destination_state_id');
            $table->unsignedBigInteger('destination_city_id');

            $table->decimal('distance', 8, 2);
            $table->unsignedBigInteger('vehicle_type_id');
            $table->decimal('amount', 10, 2)->default(0);

            $table->unsignedBigInteger('customer_id')->nullable();
            $table->enum('status', ['active', 'inactive', 'block', 'transfer', 'blacklist'])->default('active');

            $table->timestamps();
             $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_freight_charges');
    }
};
