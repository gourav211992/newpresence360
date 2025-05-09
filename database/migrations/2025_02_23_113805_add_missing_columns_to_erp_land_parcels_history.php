<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up() {
        Schema::table('erp_land_parcels_history', function (Blueprint $table) {
            $table->bigInteger('book_id')->unsigned()->nullable()->after('source_id');
            $table->date('document_date')->nullable()->after('document_no');
            $table->enum('doc_number_type', ['Auto', 'Manually'])->default('Manually')->after('document_date');
            $table->enum('doc_reset_pattern', ['Never', 'Yearly', 'Quarterly', 'Monthly'])->nullable()->after('doc_number_type');
            $table->string('doc_prefix')->nullable()->after('doc_reset_pattern');
            $table->string('doc_suffix')->nullable()->after('doc_prefix');
            $table->integer('doc_no')->nullable()->after('doc_suffix');
            $table->bigInteger('group_id')->unsigned()->nullable()->after('organization_id');
            $table->bigInteger('company_id')->unsigned()->nullable()->after('group_id');
        });
    }

    public function down() {
        Schema::table('erp_land_parcels_history', function (Blueprint $table) {
            $table->dropColumn([
                'book_id', 'document_date', 'doc_number_type', 'doc_reset_pattern', 'doc_prefix',
                'doc_suffix', 'doc_no', 'group_id', 'company_id'
            ]);
        });
    }
};
