<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_file_tracking', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_file_tracking', 'pending_signer')) {
                $table->json('pending_signer')->nullable()->after('signed_by'); // Replace 'column_name' with the appropriate column
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
            if (Schema::hasColumn('erp_file_tracking', 'pending_signer')) {
                $table->dropColumn('pending_signer');
            }
        });
    }
};
