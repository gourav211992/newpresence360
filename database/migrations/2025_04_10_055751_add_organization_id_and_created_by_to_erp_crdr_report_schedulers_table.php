<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_crdr_report_schedulers', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->after('id');
            $table->unsignedBigInteger('created_by')->nullable()->after('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_crdr_report_schedulers', function (Blueprint $table) {
            $table->dropColumn(['organization_id', 'created_by']);
        });
    }
};
