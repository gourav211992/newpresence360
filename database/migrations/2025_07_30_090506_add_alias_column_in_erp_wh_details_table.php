<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('erp_wh_details', function (Blueprint $table) {
            $table->string('heirarchy_name', 55)->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_wh_details', function (Blueprint $table) {
            $table->dropColumn('heirarchy_name');
        });
    }
};
