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
        Schema::table('erp_recoveries', function (Blueprint $table) {
            $table->string('status')->after('remarks')->nullable(); // Replace 'column_name' with the actual column name you want the 'status' field to come after
        });
    }

    public function down()
    {
        Schema::table('erp_recoveries', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

};
