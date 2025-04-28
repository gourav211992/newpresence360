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
        if (!Schema::hasColumn('erp_sale_invoices_history', 'invoice_required')) {
            Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
                $table->boolean('invoice_required')->default(0)->after('book_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('erp_sale_invoices_history', 'invoice_required')) {
            Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
                $table->dropColumn(['invoice_required']);
            });
        }
    }
};
