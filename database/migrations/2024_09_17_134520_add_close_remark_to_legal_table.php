<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCloseRemarkToLegalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_legals', function (Blueprint $table) {
            $table->text('close_remark')->nullable()->after('remark'); // Specify column order if necessary
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
            $table->dropColumn('close_remark');
        });
    }
}
