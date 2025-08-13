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
        Schema::table('stock_ledger', function (Blueprint $table) {
            // Change the column from date to timestamp
            $table->timestamp('original_receipt_date')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('stock_ledger', function (Blueprint $table) {
            // Revert back to date type
            $table->date('original_receipt_date')->change();
        });
    }
};
