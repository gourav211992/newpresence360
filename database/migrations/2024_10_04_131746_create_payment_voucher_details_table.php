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
        Schema::create('erp_payment_voucher_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_voucher_id');
            $table->unsignedBigInteger('user_id');
            $table->string('user_type');
            $table->decimal('currentAmount', 15, 2)->default(0);
            $table->decimal('orgAmount', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('payment_voucher_id')->references('id')->on('erp_payment_vouchers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_payment_voucher_details');
    }
};
