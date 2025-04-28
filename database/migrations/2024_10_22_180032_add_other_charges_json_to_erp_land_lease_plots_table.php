<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtherChargesJsonToErpLandLeasePlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_land_lease_plots', function (Blueprint $table) {
            // Adding the new column 'other_charges_json' as a JSON type
            $table->json('other_charges_json')->nullable()->after('other_charges');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_land_lease_plots', function (Blueprint $table) {
            // Dropping the 'other_charges_json' column
            $table->dropColumn('other_charges_json');
        });
    }
}
