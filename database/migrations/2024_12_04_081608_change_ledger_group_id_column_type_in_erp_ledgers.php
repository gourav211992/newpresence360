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
        Schema::table('erp_ledgers', function (Blueprint $table) {
            // Change the ledger_group_id column type to string
            $table->string('ledger_group_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_ledgers', function (Blueprint $table) {
            // Revert the column type back to its previous type (e.g., unsignedBigInteger)
            $table->unsignedBigInteger('ledger_group_id')->change();
        });
    }
};
