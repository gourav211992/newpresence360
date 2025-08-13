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
        Schema::table('upload_item_masters', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_price_currency')->nullable()->after('cost_price');
            $table->unsignedBigInteger('sell_price_currency')->nullable()->after('sell_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upload_item_masters', function (Blueprint $table) {
            $table->dropColumn('cost_price_currency');
            $table->dropColumn('sell_price_currency');
        });
    }
};
