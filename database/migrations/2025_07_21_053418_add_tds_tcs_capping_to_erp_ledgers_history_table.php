<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('erp_ledgers_history', function (Blueprint $table) {
            //
        $table->string('tds_capping')->nullable();
        $table->string('tcs_capping')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_ledgers_history', function (Blueprint $table) {
            //
            $table->dropColumn([
                'tcs_capping',
                'tds_capping',
            ]);
        });
    }
};
