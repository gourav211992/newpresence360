<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsInErpTables extends Migration
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
            $table->decimal('cost', 15, 2)->nullable()->change(); // Make 'cost' nullable
            $table->unsignedBigInteger('user_id')->nullable(); // Add 'user_id' column
            $table->string('type')->nullable(); // Add 'type' column
        });

        // Modify erp_leases table
        Schema::table('erp_leases', function (Blueprint $table) {
            $table->decimal('cost', 15, 2)->nullable()->change(); // Make 'cost' nullable
            $table->unsignedBigInteger('user_id')->nullable(); // Add 'user_id' column
            $table->string('type')->nullable(); // Add 'type' column
        });

        // Modify erp_recoveries table
        Schema::table('erp_recoveries', function (Blueprint $table) {
            $table->decimal('cost', 15, 2)->nullable()->change(); // Make 'cost' nullable
            $table->unsignedBigInteger('user_id')->nullable(); // Add 'user_id' column
            $table->string('type')->nullable(); // Add 'type' column
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
            $table->decimal('cost', 15, 2)->nullable(false)->change(); // Revert 'cost' to non-nullable
            $table->dropColumn('user_id'); // Drop 'user_id' column
            $table->dropColumn('type'); // Drop 'type' column
        });

        // Rollback changes for erp_leases table
        Schema::table('erp_leases', function (Blueprint $table) {
            $table->decimal('cost', 15, 2)->nullable(false)->change(); // Revert 'cost' to non-nullable
            $table->dropColumn('user_id'); // Drop 'user_id' column
            $table->dropColumn('type'); // Drop 'type' column
        });

        // Rollback changes for erp_recoveries table
        Schema::table('erp_recoveries', function (Blueprint $table) {
            $table->decimal('cost', 15, 2)->nullable(false)->change(); // Revert 'cost' to non-nullable
            $table->dropColumn('user_id'); // Drop 'user_id' column
            $table->dropColumn('type'); // Drop 'type' column
        });
    }
}

