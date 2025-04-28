<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMasterGroupIdToGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_groups', function (Blueprint $table) {
            // Add the master_group_id column
            $table->unsignedBigInteger('master_group_id')->nullable()->after('parent_group_id');

            // Add foreign key constraint
            $table->foreign('master_group_id')
                ->references('id')
                ->on('erp_master_groups')
                ->onDelete('set null'); // Set to null if the referenced master group is deleted
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_groups', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['master_group_id']);

            // Drop the master_group_id column
            $table->dropColumn('master_group_id');
        });
    }
}
