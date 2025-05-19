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
        Schema::create('erp_land_lease_schedulers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lease_id');
            $table->decimal('installment_cost', 15, 2)->default(0.00);
            $table->date('due_date');
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_land_lease_schedulers');
    }
};
