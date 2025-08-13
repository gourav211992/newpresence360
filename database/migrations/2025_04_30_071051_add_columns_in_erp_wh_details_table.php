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
        Schema::table('erp_wh_details', function (Blueprint $table) {
            $table->tinyInteger('is_first_level')->default(0)->after('is_storage_point')->index();
            $table->tinyInteger('is_last_level')->default(0)->after('is_first_level')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_wh_details', function (Blueprint $table) {
            $table->dropColumn('is_last_level');
            $table->dropColumn('is_first_level');
        });
    }
};
