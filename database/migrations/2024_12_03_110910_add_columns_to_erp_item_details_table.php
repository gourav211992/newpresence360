<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('erp_item_details', function (Blueprint $table) {
            // $table->string('ledger_code')->nullable()->after('ledger_id');
            $table->unsignedBigInteger('ledger_parent_id')->nullable()->after('ledger_id');
            $table->double('debit_amt_org')->nullable()->after('credit_amt');
            $table->double('credit_amt_org')->nullable()->after('debit_amt_org');
            $table->double('debit_amt_comp')->nullable()->after('credit_amt_org');
            $table->double('credit_amt_comp')->nullable()->after('debit_amt_comp');
            $table->double('debit_amt_group')->nullable()->after('credit_amt_comp');
            $table->double('credit_amt_group')->nullable()->after('debit_amt_group');
            $table->string('entry_type')->nullable()->comment('Operation voucher posting account types')-> after('credit_amt_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_item_details', function (Blueprint $table) {
            //
        });
    }
};
