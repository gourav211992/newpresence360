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
        Schema::table('erp_equip_maintenance_checklists', function (Blueprint $table) {
            $table->longText('checklist_detail')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_equip_maintenance_checklists', function (Blueprint $table) {
            $table->dropColumn('checklist_detail');
        });
    }
};
