<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_recovery_loans', function (Blueprint $table) {
            $table->string('settle_status')->nullable();
        });
    }

    public function down()
    {
        Schema::table('erp_recovery_loans', function (Blueprint $table) {
            $table->dropColumn('settle_status');
        });
    }

};
