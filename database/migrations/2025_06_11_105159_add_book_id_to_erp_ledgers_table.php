<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('erp_ledgers', function (Blueprint $table) {
        $table->unsignedBigInteger('book_id')->after('id')->nullable();
        $table->unsignedBigInteger('doc_no')->after('book_id')->nullable();
        $table->string('ledger_code_type')->after('doc_no')->nullable();
    });
}

public function down()
{
    Schema::table('erp_ledgers', function (Blueprint $table) {
        $table->dropColumn('book_id');
        $table->dropColumn('doc_no');
         $table->dropColumn('ledger_code_type');
    });
}

};
