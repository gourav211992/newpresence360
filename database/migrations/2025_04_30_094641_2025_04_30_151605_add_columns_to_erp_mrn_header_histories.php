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
        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_mrn_header_histories', 'book_code')) {
                $table->string('book_code', 255)->nullable()->after('book_id');
            }
            if (!Schema::hasColumn('erp_mrn_header_histories', 'vendor_code')) {
                $table->string('vendor_code', 255)->nullable()->after('vendor_id');
            }
            if (!Schema::hasColumn('erp_mrn_header_histories', 'currency_code')) {
                $table->string('currency_code', 255)->nullable()->after('currency_id');
            }
            if (!Schema::hasColumn('erp_mrn_header_histories', 'payment_term_id')) {
                $table->unsignedBigInteger('payment_term_id')->nullable()->after('currency_code');
            }
            if (!Schema::hasColumn('erp_mrn_header_histories', 'payment_term_code')) {
                $table->string('payment_term_code', 255)->nullable()->after('payment_term_id');
            }
            if (!Schema::hasColumn('erp_mrn_header_histories', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('mrn_header_id');
            }
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_mrn_detail_histories', 'purchase_bill_qty')) {
                $table->decimal('purchase_bill_qty', 15, 2)->default(0.00)->after('accepted_qty');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            if (Schema::hasColumn('erp_mrn_header_histories', 'book_code')) {
                $table->dropColumn('book_code');
            }
            if (Schema::hasColumn('erp_mrn_header_histories', 'vendor_code')) {
                $table->dropColumn('vendor_code');
            }
            if (Schema::hasColumn('erp_mrn_header_histories', 'currency_code')) {
                $table->dropColumn('currency_code');
            }
            if (Schema::hasColumn('erp_mrn_header_histories', 'payment_term_id')) {
                $table->dropColumn('payment_term_id');
            }
            if (Schema::hasColumn('erp_mrn_header_histories', 'payment_term_code')) {
                $table->dropColumn('payment_term_code');
            }
            if (Schema::hasColumn('erp_mrn_header_histories', 'source_id')) {
                $table->dropColumn('source_id');
            }
        });
        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            if (Schema::hasColumn('erp_mrn_header_histories', 'purchase_bill_qty')) {
                $table->dropColumn('purchase_bill_qty');
            }
        });
    }
};
