<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApproveStatusAndCloseRemarkToErpLegalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_legals', function (Blueprint $table) {
            $table->string('approve_status')->nullable()->default(null)->after('status'); // Adjust the 'after' column as necessary
            $table->text('approve_remark')->nullable()->after('approve_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_legals', function (Blueprint $table) {
            $table->dropColumn('approve_status');
            $table->dropColumn('approve_remark');
        });
    }
}

