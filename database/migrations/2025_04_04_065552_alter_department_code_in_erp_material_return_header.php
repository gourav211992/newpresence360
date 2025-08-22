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
        Schema::table('erp_material_return_header', function (Blueprint $table) {
            //
            $table->string('department_code')->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_material_return_header', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('department_code')->change()->nullable();
        });
    }
};
