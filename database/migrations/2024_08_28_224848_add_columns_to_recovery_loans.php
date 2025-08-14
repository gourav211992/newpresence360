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
        Schema::table('erp_recovery_loans', function (Blueprint $table) {
            $table->string('rec_appr_status')->comment('approve=1, reject=2')->nullable();
            $table->string('rec_appr_remark')->nullable();
            $table->string('rec_appr_doc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_recovery_loans', function (Blueprint $table) {
            $table->dropColumn(['rec_appr_status', 'rec_appr_remark', 'rec_appr_doc']);
        });
    }
};
