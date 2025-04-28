<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLegalIdToEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_emails', function (Blueprint $table) {
            $table->unsignedBigInteger('legal_id')->nullable()->after('id');

            // If you have a foreign key relationship:
            // $table->foreign('legal_id')->references('id')->on('legals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_emails', function (Blueprint $table) {
            // If you have a foreign key:
            // $table->dropForeign(['legal_id']);
            $table->dropColumn('legal_id');
        });
    }
}

