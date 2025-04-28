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
        Schema::table('erp_home_loans', function (Blueprint $table) {
            $table->string('appr_rej_recom_amnt')->nullable();
            $table->string('appr_rej_recom_remark')->nullable();
            $table->string('appr_rej_doc')->nullable();
            $table->string('appr_rej_behalf_of')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_home_loans', function (Blueprint $table) {
            $table->dropColumn(['appr_rej_recom_amnt', 'appr_rej_recom_remark', 'appr_rej_doc', 'appr_rej_behalf_of']);
        });
    }
};
