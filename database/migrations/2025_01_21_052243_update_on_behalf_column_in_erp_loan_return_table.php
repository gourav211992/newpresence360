<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_loan_return', function (Blueprint $table) {
            $table->string('on_behalf')->nullable()->change(); // Change the column to allow NULL
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_loan_return', function (Blueprint $table) {
            $table->string('on_behalf')->nullable(false)->change(); // Revert back to NOT NULL if needed
        });
    }
};
