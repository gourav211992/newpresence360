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
            $table->text('book_terms') -> default(null) -> after('total_amount');
            $table->unsignedBigInteger('book_terms_id') -> nullable() -> after('book_terms');

            $table->text('customer_terms') -> default(null) -> after('book_terms_id');
            $table->unsignedBigInteger('customer_terms_id') -> nullable() -> after('customer_terms');
        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->text('book_terms') -> default(null) -> after('total_amount');
            $table->unsignedBigInteger('book_terms_id') -> nullable() -> after('book_terms');

            $table->text('customer_terms') -> default(null) -> after('book_terms_id');
            $table->unsignedBigInteger('customer_terms_id') -> nullable() -> after('customer_terms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->dropColumn(['book_terms', 'book_terms_id', 'customer_terms', 'customer_terms_id']);
        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->dropColumn(['book_terms', 'book_terms_id', 'customer_terms', 'customer_terms_id']);
        });
    }
};
