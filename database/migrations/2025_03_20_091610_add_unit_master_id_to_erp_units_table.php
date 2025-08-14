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
        Schema::table('erp_units', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_master_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_units', function (Blueprint $table) {
            $table->dropColumn('unit_master_id');
        });
    }
};
