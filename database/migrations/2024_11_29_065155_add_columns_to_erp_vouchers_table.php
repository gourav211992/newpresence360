<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_vouchers', function (Blueprint $table) {
            $table->date('document_date')->nullable()->after('voucher_no');
            $table->enum('doc_number_type', ConstantHelper::DOC_NO_TYPES)->default(ConstantHelper::DOC_NO_TYPE_MANUAL)->after('document_date');
            $table->enum('doc_reset_pattern', ConstantHelper::DOC_RESET_PATTERNS)->nullable()->default(null)->after('doc_number_type');
            $table->string('doc_prefix')->nullable()->after('doc_reset_pattern');
            $table->string('doc_suffix')->nullable()->after('doc_prefix');
            $table->integer('doc_no')->nullable()->after('doc_suffix');
            $table->string('document_status')->after('approvalStatus')->nullable()->comment('Status of the document');

        });
    }

    public function down()
    {
        Schema::table('erp_vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'document_date',
                'doc_number_type',
                'doc_reset_pattern',
                'doc_prefix',
                'doc_suffix',
                'doc_no',
                'document_status'

            ]);
        });
    }
};
