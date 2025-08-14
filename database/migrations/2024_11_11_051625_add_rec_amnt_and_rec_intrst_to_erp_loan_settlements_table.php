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
    Schema::table('erp_loan_settlements', function (Blueprint $table) {
        $table->decimal('rec_amnt', 15, 2)->default(0)->after('settle_application_no'); // replace 'existing_column' with the column you want these to follow
        $table->decimal('rec_intrst', 15, 2)->default(0)->after('rec_amnt');
    });
}

public function down()
{
    Schema::table('erp_loan_settlements', function (Blueprint $table) {
        $table->dropColumn(['rec_amnt', 'rec_intrst']);
    });
}

};
