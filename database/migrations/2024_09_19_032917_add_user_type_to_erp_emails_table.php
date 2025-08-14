<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserTypeToErpEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_emails', function (Blueprint $table) {
            $table->string('user_type')->nullable(); // Add the user_type column
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
            $table->dropColumn('user_type'); // Remove the user_type column
        });
    }
}

