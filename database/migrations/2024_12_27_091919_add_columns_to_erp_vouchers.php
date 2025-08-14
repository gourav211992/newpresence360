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
        Schema::table('erp_vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_vouchers', 'reference_service')) {
                $table->string('reference_service')->nullable()->after('group_currency_exg_rate')->comment('Alias of operation service');
            }
            if (!Schema::hasColumn('erp_vouchers', 'reference_doc_id')) {
                $table->unsignedBigInteger('reference_doc_id')->nullable()->after('reference_service')->comment('Id of operation document');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('erp_vouchers', 'reference_service')) {
                $table->dropColumn('reference_service');
            }
            if (Schema::hasColumn('erp_vouchers', 'reference_doc_id')) {
                $table->dropColumn('reference_doc_id');
            }
        });
    }
};
