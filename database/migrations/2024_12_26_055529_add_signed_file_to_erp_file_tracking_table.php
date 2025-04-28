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
            $table->string('signed_file')->nullable()->after('file'); // Add this line to add the signed_file column
        });
    }

    public function down()
    {
        Schema::table('erp_file_tracking', function (Blueprint $table) {
            $table->dropColumn('signed_file'); // This will drop the signed_file column if the migration is rolled back
        });
    }
};
