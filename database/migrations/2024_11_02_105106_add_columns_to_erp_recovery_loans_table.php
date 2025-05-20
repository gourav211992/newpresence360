<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToErpRecoveryLoansTable extends Migration
{
    public function up()
    {
        Schema::table('erp_recovery_loans', function (Blueprint $table) {
            $table->decimal('bal_interest_amnt', 15, 2)->nullable()->after('balance_amount');
            $table->string('account_number')->nullable()->after('bal_interest_amnt');
            $table->decimal('dis_amount', 15, 2)->nullable()->after('account_number');
            $table->json('dis_id')->nullable()->after('dis_amount');
        });
    }

    public function down()
    {
        Schema::table('erp_recovery_loans', function (Blueprint $table) {
            $table->dropColumn([
                'balance_amount',
                'bal_interest_amnt',
                'account_number',
                'dis_amount',
                'dis_id',
            ]);
        });
    }
}
