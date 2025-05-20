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
    Schema::table('erp_legals', function (Blueprint $table) {
        $table->string('appr_rej_recom_remark')->nullable();
        $table->string('appr_rej_doc')->nullable();
        $table->string('appr_rej_behalf_of')->nullable();
        $table->integer('approvalLevel')->default(0);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('erp_legals', function (Blueprint $table) {
        $table->dropColumn('appr_rej_recom_remark');
        $table->dropColumn('appr_rej_doc');
        $table->dropColumn('appr_rej_behalf_of');
        $table->dropColumn('approvalLevel');
    });
}

};
