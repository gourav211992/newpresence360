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
        Schema::table('erp_land_plots', function (Blueprint $table) {
            $table->json('attachments')->nullable(); // Adding a nullable JSON field for attachments
        });
    }

    public function down()
    {
        Schema::table('erp_land_plots', function (Blueprint $table) {
            $table->dropColumn('attachments'); // Remove the attachments column
        });
    }
};
