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
        Schema::table('erp_items', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_price_currency_id')->nullable()->after('cost_price');
            $table->unsignedBigInteger('sell_price_currency_id')->nullable()->after('sell_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropColumn(['cost_price_currency_id','sell_price_currency_id']);
        });
    }
};
