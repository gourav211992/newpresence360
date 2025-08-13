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
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            if (Schema::hasColumn('erp_pslip_items', 'machine_id')) {
                $table->dropColumn('machine_id');
            }
        });
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->json('machine_id')->nullable()->after('mo_product_id')->comment('JSON array of machine IDs');
            $table->unsignedTinyInteger('cycle_count')->nullable()->after('machine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->dropColumn('cycle_count');
        });
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->dropColumn('machine_id');
        });
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->unsignedBigInteger('machine_id')->nullable()->after('mo_product_id');
        });
    }
};
