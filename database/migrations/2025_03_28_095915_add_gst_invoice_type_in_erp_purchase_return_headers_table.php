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
        Schema::table('erp_purchase_return_headers', function (Blueprint $table) {
            $table->string('gst_invoice_type', 10)->default('B2B')->after('doc_number_type');
        });
        Schema::table('erp_purchase_return_headers_history', function (Blueprint $table) {
            $table->string('gst_invoice_type', 10)->default('B2B')->after('doc_number_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_purchase_return_headers', function (Blueprint $table) {
            $table->dropColumn(['gst_invoice_type']);
        });
        Schema::table('erp_purchase_return_headers_history', function (Blueprint $table) {
            $table->dropColumn(['gst_invoice_type']);
        });
    }
};
