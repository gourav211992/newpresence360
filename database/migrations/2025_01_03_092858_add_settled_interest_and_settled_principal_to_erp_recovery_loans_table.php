<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_recovery_loans', function (Blueprint $table) {
            $table->decimal('settled_interest', 15, 2)->nullable()->after('rec_interest_amnt');
            $table->decimal('settled_principal', 15, 2)->nullable()->after('settled_interest');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_recovery_loans', function (Blueprint $table) {
            $table->dropColumn(['settled_interest', 'settled_principal']);
        });
    }
};
