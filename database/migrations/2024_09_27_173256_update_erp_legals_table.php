<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateErpLegalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_legals', function (Blueprint $table) {
            // Make mobile_number and email nullable
            $table->string('phone')->nullable()->change();
            $table->string('email')->nullable()->change();

            // Add filenumber and address columns
            $table->string('filenumber')->nullable();
            $table->string('address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_legals', function (Blueprint $table) {
            // Revert the changes
            $table->string('phone')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();

            // Drop filenumber and address columns
            $table->dropColumn('filenumber');
            $table->dropColumn('address');
        });
    }
}


