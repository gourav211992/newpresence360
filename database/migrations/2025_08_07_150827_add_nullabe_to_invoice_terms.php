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
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->text('book_terms') -> default(null) -> nullable()-> change();

            $table->text('customer_terms') -> default(null) -> nullable() -> change();
        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->text('book_terms') -> default(null) -> nullable() ->  change();

            $table->text('customer_terms') -> default(null) -> nullable() -> change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->text('book_terms') -> default(null) -> nullable(false)-> change();

            $table->text('customer_terms') -> default(null) -> nullable(false) -> change();
        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->text('book_terms') -> default(null) -> nullable(false) ->  change();

            $table->text('customer_terms') -> default(null) -> nullable(false) -> change();
        });
    }
};
