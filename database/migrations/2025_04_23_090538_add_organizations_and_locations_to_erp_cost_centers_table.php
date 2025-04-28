<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('erp_cost_centers', function (Blueprint $table) {
        $table->json('organizations')->nullable()->after('id');
        $table->json('locations')->nullable()->after('organizations');
        $table->dropForeign(['cost_group_id']);
 });
}

public function down()
{
    Schema::table('erp_cost_centers', function (Blueprint $table) {
        $table->dropColumn(['organizations', 'locations']);
});
}
};
