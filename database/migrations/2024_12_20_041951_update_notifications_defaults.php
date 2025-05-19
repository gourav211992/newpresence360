<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('auth_type', 199)->nullable()->change();
            $table->unsignedInteger('auth_id')->nullable()->change();
            $table->string('type', 199)->nullable()->change();
            $table->unsignedInteger('type_id')->nullable()->change();
            $table->string('title', 299)->nullable()->change();
            $table->text('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('auth_type', 199)->nullable(false)->change();
            $table->unsignedInteger('auth_id')->nullable(false)->change();
            $table->string('type', 199)->nullable(false)->change();
            $table->unsignedInteger('type_id')->nullable()->default(null)->change();
            $table->string('title', 299)->nullable(false)->change();
            $table->text('description')->nullable()->default(null)->change();
        });
    }
};
