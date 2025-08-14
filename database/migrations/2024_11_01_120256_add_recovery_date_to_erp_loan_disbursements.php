<?php use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecoveryDateToErpLoanDisbursements extends Migration
{
    public function up()
    {
        Schema::table('erp_loan_disbursements', function (Blueprint $table) {
            $table->date('recovery_date')->nullable();
        });
    }

    public function down()
    {
        Schema::table('erp_loan_disbursements', function (Blueprint $table) {
            $table->dropColumn('recovery_date');
        });
    }
}
