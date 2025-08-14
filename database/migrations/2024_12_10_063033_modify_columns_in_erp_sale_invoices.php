<?php

use App\Helpers\ConstantHelper;
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
        if (!Schema::hasColumn('erp_sale_invoices', 'document_type')) {
            Schema::table('erp_sale_invoices', function (Blueprint $table) {
                $table->enum('document_type', ConstantHelper::SALE_INVOICE_DOC_TYPES) -> default(ConstantHelper::SI_SERVICE_ALIAS) -> after('document_status');
            });
        }
        DB::statement("ALTER TABLE `erp_sale_invoices` MODIFY COLUMN `document_type` ENUM('si', 'lease-invoice', 'dnote', 'sinvdnote')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `erp_sale_invoices` MODIFY COLUMN `document_type` ENUM('si', 'lease-invoice', 'dnote', 'sinvdnote')");
        if (Schema::hasColumn('erp_sale_invoices', 'document_type')) {
            Schema::table('erp_sale_invoices', function (Blueprint $table) {
                $table->dropColumn('document_type');
            });
        }
    }
};
