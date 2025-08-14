<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusAndApprovalStatusToVoucherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_vouchers', function (Blueprint $table) {
            $table->integer('status')->default(0); // Add status as integer with a default value
            $table->integer('approvalLevel')->default(0); // Add approvalLevel as a integer
            $table->string('approvalStatus')->default('completed'); // Add approvalStatus as a string
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_vouchers', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('approvalLevel');
            $table->dropColumn('approvalStatus');
        });
    }
}
