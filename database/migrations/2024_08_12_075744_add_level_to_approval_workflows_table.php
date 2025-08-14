<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLevelToApprovalWorkflowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if the column doesn't exist
        if (!Schema::hasColumn('erp_approval_workflows', 'level')) {
            Schema::table('erp_approval_workflows', function (Blueprint $table) {
                $table->integer('level')->after('user_id')->notNullable();
            });
        }
    }

    public function down()
    {
        // Optional: Drop the column if needed when rolling back
        Schema::table('erp_approval_workflows', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
}
