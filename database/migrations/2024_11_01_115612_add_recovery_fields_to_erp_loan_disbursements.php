<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecoveryFieldsToErpLoanDisbursements extends Migration
{
    public function up()
    {
        Schema::table('erp_loan_disbursements', function (Blueprint $table) {
            $table->decimal('recovered', 15, 2)->nullable();
            $table->decimal('balance', 15, 2)->nullable()->after('recovered');
            $table->decimal('interest', 15, 2)->nullable()->after('balance');
            $table->decimal('settled_interest', 15, 2)->nullable()->after('interest');
            $table->decimal('settled_principal', 15, 2)->nullable()->after('settled_interest');
            $table->decimal('remaining', 15, 2)->nullable()->after('settled_principal');
        });
    }

    public function down()
    {
        Schema::table('erp_loan_disbursements', function (Blueprint $table) {
            $table->dropColumn(['recovered', 'balance', 'interest', 'settled_interest', 'settled_principal', 'remaining']);
        });
    }
}
