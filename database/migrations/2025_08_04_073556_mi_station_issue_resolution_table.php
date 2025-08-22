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
        $dbName = DB::connection()->getDatabaseName();
        if ($dbName == "staqo_presence_kurlon") {
            DB::table('stock_ledger') -> where('book_type', 'mi') -> where('transaction_type', 'receipt') -> where('document_header_id', 176) -> whereNull('station_id') -> update([
                'station_id' => 29
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dbName = DB::connection()->getDatabaseName();
        if ($dbName == "staqo_presence_kurlon") {
            DB::table('stock_ledger') -> where('book_type', 'mi') -> where('transaction_type', 'receipt') -> where('document_header_id', 176) -> whereNull('station_id') -> update([
                'station_id' => null
            ]);
        }
    }
};
