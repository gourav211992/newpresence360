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
        Schema::table('erp_currency_exchanges', function (Blueprint $table) {
            $table->decimal('exchange_rate', 15, 2)->default(0.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_currency_exchanges', function (Blueprint $table) {
            $table->decimal('exchange_rate', 15, 6)->default(0.000000)->change();
        });
    }
};
