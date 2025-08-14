<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyApprovalWorkflowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_approval_workflows', function (Blueprint $table) {
            // Modify 'user_id' column to be an integer
            $table->integer('user_id')->change();
            
            // Add a new column for 'user_type'
            $table->string('user_type')->nullable()->after('user_id'); // Adding user_type column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_approval_workflows', function (Blueprint $table) {
            // Reverse the changes

            // Change 'user_id' back to its original type (adjust accordingly if it was a specific type, such as string)
            // $table->string('user_id')->change(); // Uncomment and adjust based on the original type of user_id

            // Drop the 'user_type' column if it exists
            if (Schema::hasColumn('erp_approval_workflows', 'user_type')) {
                $table->dropColumn('user_type');
            }
        });
    }
}
