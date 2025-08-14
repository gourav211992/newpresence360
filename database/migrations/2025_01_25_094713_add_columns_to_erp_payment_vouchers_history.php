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
        Schema::table('erp_payment_vouchers_history', function (Blueprint $table) {
            $table->date('document_date');
            $table->enum('doc_number_type', ['Auto', 'Manually'])->after('document_date')->nullable();
            $table->enum('doc_reset_pattern', ['Never', 'Yearly', 'Quarterly', 'Monthly'])->after('doc_number_type');
            $table->string('doc_prefix', 255)->after('doc_reset_pattern')->nullable();
            $table->string('doc_suffix', 255)->nullable()->after('doc_prefix');
            $table->integer('doc_no')->after('doc_suffix')->nullable();
            $table->bigInteger('ledger_group_id')->after('doc_no')->nullable();
            $table->string('document_status', 255)->after('ledger_group_id')->nullable();   
            $table->integer('approval_level')->after('document_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_payment_vouchers_history', function (Blueprint $table) {
            $table->dropColumn([
                'document_date',
                'doc_number_type',
                'doc_reset_pattern',
                'doc_prefix',
                'doc_suffix',
                'doc_no',
                'ledger_group_id',
                'document_status',
                'approval_level'
            ]);
        });
    }
};
