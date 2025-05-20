<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('erp_land_leases_actions', function (Blueprint $table) {
        $table->unsignedBigInteger('user_id')->nullable(); // Assuming user_id is a foreign key
        $table->string('user_type')->nullable(); // If using polymorphic relations
    });
}

public function down()
{
    Schema::table('erp_land_leases_actions', function (Blueprint $table) {
        $table->dropColumn('user_id');
        $table->dropColumn('user_type');
    });
}

};
