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
        Schema::table('stock_ledger_reservations', function (Blueprint $table) {
            $table->dropColumn(['pslip_id', 'pslip_item_id', 'so_id', 'so_item_id']);
            $table->string('book_type', 50) -> nullable() -> index() -> after('id');
            $table->unsignedBigInteger('header_id') -> nullable() -> index() -> after('book_type');
            $table->unsignedBigInteger('detail_id') -> nullable() -> index() -> after('header_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_ledger_reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('pslip_id') -> nullable();
            $table->unsignedBigInteger('pslip_item_id') -> nullable();
            $table->unsignedBigInteger('so_id') -> nullable();
            $table->unsignedBigInteger('so_item_id') -> nullable();
        });
    }
};
