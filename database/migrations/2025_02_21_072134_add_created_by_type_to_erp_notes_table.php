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
        Schema::table('erp_notes', function (Blueprint $table) {
            $table->string('created_by_type', 100)->nullable(); 
        });
    }

    public function down()
    {
        Schema::table('erp_notes', function (Blueprint $table) {
            $table->dropColumn('created_by_type'); 
        });
    }
};
