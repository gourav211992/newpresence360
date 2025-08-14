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
        Schema::table('erp_sale_returns', function (Blueprint $table) {
            $table->string('gst_invoice_type', 10)->default('B2B')->after('document_type');
            $table->string('e_invoice_status', 25)->nullable()->after('gst_invoice_type');
        });
        Schema::table('erp_sale_return_histories', function (Blueprint $table) {
            $table->string('gst_invoice_type', 10)->default('B2B')->after('document_type');
            $table->string('e_invoice_status', 25)->nullable()->after('gst_invoice_type');
        });
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->string('e_invoice_status', 25)->nullable()->after('gst_invoice_type');
        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->string('e_invoice_status', 25)->nullable()->after('gst_invoice_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_sale_returns', function (Blueprint $table) {
            $table->dropColumn(['gst_invoice_type', 'e_invoice_status']);
        });
        Schema::table('erp_sale_return_histories', function (Blueprint $table) {
            $table->dropColumn(['gst_invoice_type', 'e_invoice_status']);
        });
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->dropColumn(['e_invoice_status']);
        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->dropColumn(['e_invoice_status']);
        });
    }
};
