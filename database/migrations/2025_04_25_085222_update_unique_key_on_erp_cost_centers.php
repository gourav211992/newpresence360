<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUniqueKeyOnErpCostCenters extends Migration
{
    public function up()
    {
        Schema::table('erp_cost_centers', function (Blueprint $table) {
            // Drop the existing unique index on name
            $table->dropUnique('erp_cost_centers_name_unique');

            // Add composite unique index on group_id, organization_id, and name
            $table->unique(['group_id', 'organization_id', 'name'], 'erp_cost_centers_group_org_name_unique');
        });
    }

    public function down()
    {
        Schema::table('erp_cost_centers', function (Blueprint $table) {
            // Drop the composite unique key
            $table->dropUnique('erp_cost_centers_group_org_name_unique');

            // Restore the old unique key on name
            $table->unique('name', 'erp_cost_centers_name_unique');
        });
    }
}
