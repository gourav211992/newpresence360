<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_land_parcels', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->after('organization_id'); // Replace 'column_name' with the appropriate column after which you want to add this field
        });
    }

    public function down()
    {
        Schema::table('erp_land_parcels', function (Blueprint $table) {
            $table->dropColumn('group_id');
        });
    }
};
