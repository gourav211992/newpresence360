<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
        $table->unsignedBigInteger('reference_doc_id')->nullable()->after('book_date');
        $table->string('reference_series')->nullable()->after('reference_doc_id');
    });

    Schema::table('erp_finance_fixed_asset_registration_history', function (Blueprint $table) {
        $table->unsignedBigInteger('reference_doc_id')->nullable()->after('book_date');
        $table->string('reference_series')->nullable()->after('reference_doc_id');
    });
}

public function down()
{
    Schema::table('erp_finance_fixed_asset_registration', function (Blueprint $table) {
        $table->dropColumn(['reference_doc_id', 'reference_series']);
    });

    Schema::table('erp_finance_fixed_asset_registration_history', function (Blueprint $table) {
        $table->dropColumn(['reference_doc_id', 'reference_series']);
    });
}

};
