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
        Schema::create('erp_payment_vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->integer('voucher_no')->unique();
            $table->string('document_type');
            $table->date('date');
            $table->string('payment_type');
            $table->unsignedInteger('bank_id')->nullable();
            $table->unsignedInteger('account_id')->nullable();
            $table->unsignedBigInteger('ledger_id')->nullable();
            $table->unsignedInteger('currency_id');
            $table->string('exchange_rate');
            $table->string('document')->nullable();
            $table->string('remarks')->nullable();
            $table->decimal('amount', 15, 2);

            $table->integer('approvalLevel')->default(1);
            $table->string('approvalStatus');

            $table->unsignedInteger('user_id');
            $table->string('user_type');

            $table->unsignedInteger('group_id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('organization_id');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_payment_vouchers');
    }
};
