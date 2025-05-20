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
    Schema::table('erp_loan_disbursements', function (Blueprint $table) {
        $table->string('recovery_status')->nullable()->after('recovery_date'); // Replace 'existing_column' with the name of the column after which you want to add `recovery_status`
    });
}

public function down()
{
    Schema::table('erp_loan_disbursements', function (Blueprint $table) {
        $table->dropColumn('recovery_status');
    });
}
};
