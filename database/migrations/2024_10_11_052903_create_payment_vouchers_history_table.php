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
        Schema::create('erp_payment_vouchers_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('book_id');
            $table->string('bookCode');
            $table->integer('voucher_no')->unique();
            $table->string('document_type');
            $table->date('date');
            $table->string('payment_type');
            $table->unsignedInteger('bank_id')->nullable();
            $table->string('bankCode')->nullable();
            $table->string('payment_mode')->nullable();
            $table->string('reference_no')->nullable();

            $table->unsignedInteger('account_id')->nullable();
            $table->string('accountNo')->nullable();
            $table->unsignedBigInteger('ledger_id')->nullable();
            $table->unsignedInteger('currency_id');
            $table->string('currencyCode');

            $table->integer('org_currency_id')->nullable();
            $table->string('org_currency_code')->nullable();
            $table->string('org_currency_exg_rate')->nullable();

            $table->integer('comp_currency_id')->nullable();
            $table->string('comp_currency_code')->nullable();
            $table->string('comp_currency_exg_rate')->nullable();

            $table->integer('group_currency_id')->nullable();
            $table->string('group_currency_code')->nullable();
            $table->string('group_currency_exg_rate')->nullable();

            $table->string('document')->nullable();
            $table->string('remarks')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date')->nullable();

            $table->integer('approvalLevel')->default(1);
            $table->string('approvalStatus');
            $table->integer('revision_number')->default(0);
            $table->date('revision_date')->nullable();

            $table->unsignedInteger('user_id');
            $table->string('user_type');

            $table->unsignedInteger('group_id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('organization_id');
        
            $table->timestamps();
        });

        Schema::create('erp_payment_voucher_details_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('payment_voucher_id');
            $table->unsignedInteger('party_id');
            $table->string('party_type');
            $table->string('type');
            $table->string('partyCode');
            $table->decimal('currentAmount', 15, 2)->default(0);
            $table->decimal('orgAmount', 15, 2)->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_payment_vouchers_history');
        Schema::dropIfExists('erp_payment_voucher_details_history');
    }
};
