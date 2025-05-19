<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraOtherchargesJsonToErpLandLeasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_land_leases', function (Blueprint $table) {
            // Add the extra_othercharges_json column as JSON type
            $table->json('extra_othercharges_json')->nullable()->after('otherextra_charges');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_land_leases', function (Blueprint $table) {
            // Drop the extra_othercharges_json column if rollback
            $table->dropColumn('extra_othercharges_json');
        });
    }
}

