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
        Schema::table('erp_cost_centers', function (Blueprint $table) {
            // Drop existing composite unique index on (group_id, organization_id, name)
            $table->dropUnique('erp_cost_centers_group_org_name_unique');

            // Recreate unique index on only (group_id, organization_id)
            $table->unique(['group_id', 'organization_id'], 'erp_cost_centers_group_org_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('erp_cost_centers', function (Blueprint $table) {
            // Drop the new unique index
            $table->dropUnique('erp_cost_centers_group_org_name_unique');

            // Restore original composite unique index
            $table->unique(['group_id', 'organization_id', 'name'], 'erp_cost_centers_group_org_name_unique');
        });
    }
};
