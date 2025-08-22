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
            $table->string('receipt_book_type', 50) -> nullable() -> index() -> after('issue_book_type');
            $table->unsignedBigInteger('receipt_header_id') -> nullable() -> index() -> after('issue_header_id');
            $table->unsignedBigInteger('receipt_detail_id') -> nullable() -> index() -> after('issue_detail_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_ledger_reservations', function (Blueprint $table) {
            $table->dropColumn(['receipt_book_type', 'receipt_header_id', 'receipt_detail_id']);
        });
    }
};
