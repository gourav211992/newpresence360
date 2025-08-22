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
        Schema::create('erp_bank_statements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->unsignedBigInteger('ledger_id')->index();
            $table->unsignedBigInteger('ledger_group_id')->index();
            $table->unsignedBigInteger('bank_id')->index();
            $table->string('account_number')->index();
            $table->string('narration',255)->index();
            $table->string('ref_no',255)->index();
            $table->double('debit_amt')->default(0);
            $table->double('credit_amt')->default(0);
            $table->double('balance')->default(0);
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_bank_statements');
    }
};


