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
        DB::statement("ALTER TABLE stock_ledger_reservations CHANGE header_id issue_header_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE stock_ledger_reservations CHANGE detail_id issue_detail_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE stock_ledger_reservations CHANGE book_type issue_book_type VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE stock_ledger_reservations CHANGE issue_header_id header_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE stock_ledger_reservations CHANGE issue_detail_id detail_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE stock_ledger_reservations CHANGE issue_book_type book_type VARCHAR(255) NULL");
    }
};
