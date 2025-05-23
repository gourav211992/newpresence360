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
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->decimal('sell_price', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upload_item_masters', function (Blueprint $table) {
            $table->dropColumn('cost_price');
            $table->dropColumn('sell_price');
        });
    }
};
