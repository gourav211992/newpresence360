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
        Schema::table('erp_land_leases_history', function (Blueprint $table) {
            $table->text('appr_rej_recom_remark')->nullable()->after('approvalStatus'); // Remarks for approval/rejection
            $table->text('appr_rej_doc')->nullable()->after('appr_rej_recom_remark'); // Document for approval/rejection
            $table->string('appr_rej_behalf_of')->nullable()->after('appr_rej_doc'); // Approval or rejection on behalf of someone else

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_land_leases_history', function (Blueprint $table) {
            $table->dropColumn('appr_rej_recom_remark');
            $table->dropColumn('appr_rej_doc');
            $table->dropColumn('appr_rej_behalf_of');
            //
        });
    }
};
