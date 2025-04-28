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
        Schema::table('erp_land_leases', function (Blueprint $table) {
            $table->text('appr_rej_recom_remark')->collation('utf8mb4_unicode_ci')->nullable();
            $table->string('appr_rej_doc', 255)->collation('utf8mb4_unicode_ci')->nullable();
            $table->string('appr_rej_behalf_of', 255)->collation('utf8mb4_unicode_ci')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_land_leases', function (Blueprint $table) {
            $table->dropColumn(['appr_rej_recom_remark', 'appr_rej_doc', 'appr_rej_behalf_of']);
        });
    }
};
