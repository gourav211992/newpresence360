<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('erp_file_tracking', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_file_tracking', 'approval_teams')) {
                $table->json('approval_teams')->nullable()->after('comment'); // Replace 'column_name' with the appropriate column
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_file_tracking', function (Blueprint $table) {
            if (Schema::hasColumn('erp_file_tracking', 'approval_teams')) {
                $table->dropColumn('approval_teams');
            }
        });
    }
};
