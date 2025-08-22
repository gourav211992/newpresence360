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
            $table->unsignedBigInteger('machine_id')->nullable()->after('mo_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pslip_items', function (Blueprint $table) {
            $table->dropColumn('machine_id');
        });
    }
};
