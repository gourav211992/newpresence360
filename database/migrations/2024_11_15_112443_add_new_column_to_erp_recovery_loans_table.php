<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_recovery_loans', function (Blueprint $table) {
        $table->unsignedBigInteger('book_id')->nullable()->after('id');
        $table->date('document_date')->nullable()->after('document_no');
	$table->enum('doc_number_type', ConstantHelper::DOC_NO_TYPES)->default(ConstantHelper::DOC_NO_TYPE_MANUAL)->after('document_no');
	$table->enum('doc_reset_pattern', ConstantHelper::DOC_RESET_PATTERNS)->nullable()->default(null)->after('doc_number_type');
	$table->string('doc_prefix')->nullable()->after('doc_reset_pattern');
	$table->string('doc_suffix')->nullable()->after('doc_prefix');
	$table->integer('doc_no')->nullable()->after('doc_suffix');
        });
    }

    public function down()
    {
        Schema::table('erp_recovery_loans', function (Blueprint $table) {
            $table->dropColumn([
                'book_id',
                'document_date',
                'doc_number_type',
                'doc_reset_pattern',
                'doc_prefix',
                'doc_suffix',
                'doc_no'
            ]);
        });
    }
};
