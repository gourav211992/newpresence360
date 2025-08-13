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
        Schema::create('erp_failed_bank_statements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->unsignedBigInteger('ledger_id')->index();
            $table->unsignedBigInteger('ledger_group_id')->index();
            $table->unsignedBigInteger('bank_id')->index();
            $table->unsignedBigInteger('account_id')->nullable()->index();
            $table->string('uid','200')->nullable()->index();
            $table->string('account_number')->index();
            $table->string('narration',255)->nullable();
            $table->string('ref_no',255)->nullable();
            $table->double('debit_amt')->nullable()->default(0);
            $table->double('credit_amt')->default(0);
            $table->double('balance')->default(0);
            $table->date('date')->nullable();
            $table->text('errors')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index('created_by_index');
            $table->string('created_by_type')->nullable()->index('created_by_type_index');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_failed_bank_statements');
    }
};
