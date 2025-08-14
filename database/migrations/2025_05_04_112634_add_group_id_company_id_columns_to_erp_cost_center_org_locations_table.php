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
        Schema::table('erp_cost_center_org_locations', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('group_id')->nullable()->after('organization_id');
            $table->unsignedBigInteger('company_id')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_cost_center_org_locations', function (Blueprint $table) {
            $table->dropColumn(['group_id', 'company_id']);
        });
    }
};
