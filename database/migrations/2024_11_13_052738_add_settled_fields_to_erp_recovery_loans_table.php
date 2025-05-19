<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_recovery_loans', function (Blueprint $table) {
            $table->decimal('settled_blnc_amnt', 15, 2)->nullable();
            $table->decimal('settled_amnt', 15, 2)->nullable();
            $table->decimal('settled_rec_amnt', 15, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('erp_recovery_loans', function (Blueprint $table) {
            $table->dropColumn(['settled_blnc_amnt', 'settled_amnt', 'settled_rec_amnt']);
        });
    }

};
