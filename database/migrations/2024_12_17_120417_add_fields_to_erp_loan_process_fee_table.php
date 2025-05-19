<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('erp_loan_process_fee', function (Blueprint $table) {
            $table->string('payment_type')->nullable()->after('fee_amount'); // Replace 'existing_column' with the last column in the table
            $table->date('payment_date')->nullable()->after('payment_type');
            $table->unsignedBigInteger('bank_id')->nullable()->after('payment_date');
            $table->unsignedBigInteger('account_id')->nullable()->after('bank_id');
            $table->string('payment_mode')->nullable()->after('account_id');
            $table->string('reference_no')->nullable()->after('payment_mode');
            $table->unsignedBigInteger('ledger_id')->nullable()->after('reference_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_loan_process_fee', function (Blueprint $table) {
            $table->dropColumn([
                'payment_type',
                'payment_date',
                'bank_id',
                'account_id',
                'payment_mode',
                'reference_no',
                'ledger_id',
            ]);
        });
    }
};
