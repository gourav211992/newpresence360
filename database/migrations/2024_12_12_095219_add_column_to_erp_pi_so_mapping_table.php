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
        Schema::table('erp_pi_so_mapping', function (Blueprint $table) {
            $table->unsignedBigInteger('child_bom_id')->nullable()->after('attributes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pi_so_mapping', function (Blueprint $table) {
            $table->dropColumn('child_bom_id');
        });
    }
};
