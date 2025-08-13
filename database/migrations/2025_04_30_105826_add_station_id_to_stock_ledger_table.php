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
        Schema::table('stock_ledger', function (Blueprint $table) {
            $table->unsignedBigInteger('station_id')->after('sub_store_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_ledger', function (Blueprint $table) {
            $table->dropColumn(['station_id']);
        });
    }
};
