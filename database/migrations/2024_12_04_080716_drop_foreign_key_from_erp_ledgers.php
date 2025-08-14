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
            // Drop the foreign key for ledger_group_id
            $table->dropForeign(['ledger_group_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_ledgers', function (Blueprint $table) {
            // Re-add the foreign key for ledger_group_id
            $table->foreign('ledger_group_id')
                ->references('id')
                ->on('ledger_groups') // Replace with the actual table name
                ->onDelete('cascade'); // Replace with the actual onDelete rule if different
        });
    }
};
