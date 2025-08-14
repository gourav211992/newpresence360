<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('erp_number_patterns', function (Blueprint $table) {
            $table->string('prefix')->nullable()->change();
            $table->string('suffix')->nullable()->change();
            $table->integer('starting_no')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_number_patterns', function (Blueprint $table) {
            //
        });
    }
};
