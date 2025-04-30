<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_crdr_report_schedulers', function (Blueprint $table) {
            $table->json('cc')->nullable()->after('type'); 
        });
    }

    public function down()
    {
        Schema::table('erp_crdr_report_schedulers', function (Blueprint $table) {
            $table->dropColumn('cc');
        });
    }
};
