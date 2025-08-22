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
        Schema::create('erp_rfq_pi_mapping_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pi_rfq_id');
            $table->unsignedBigInteger('rfq_id');
            $table->unsignedBigInteger('pi_id');
            $table->unsignedBigInteger('pi_item_id');
            $table->unsignedBigInteger('rfq_item_id');
            $table->unsignedBigInteger('rfq_qty');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_rfq_pi_mapping_details');
    }
};
