<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrganizationIdToErpTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modify erp_lands table
        Schema::table('erp_lands', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(); // Add 'organization_id' column
        });

        // Modify erp_leases table
        Schema::table('erp_leases', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(); // Add 'organization_id' column
        });

        // Modify erp_recovery table
        Schema::table('erp_recoveries', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(); // Add 'organization_id' column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Rollback changes for erp_lands table
        Schema::table('erp_lands', function (Blueprint $table) {
            $table->dropColumn('organization_id'); // Drop 'organization_id' column
        });

        // Rollback changes for erp_leases table
        Schema::table('erp_leases', function (Blueprint $table) {
            $table->dropColumn('organization_id'); // Drop 'organization_id' column
        });

        // Rollback changes for erp_recovery table
        Schema::table('erp_recoveries', function (Blueprint $table) {
            $table->dropColumn('organization_id'); // Drop 'organization_id' column
        });
    }
}

