<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRevisionFieldsToErpLandLeasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_land_leases', function (Blueprint $table) {
            $table->integer('revision_number')->default(0)->after('approvalStatus');
            $table->date('revision_date')->nullable()->after('revision_number');
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
            $table->dropColumn('revision_number');
            $table->dropColumn('revision_date');
        });
    }
}
