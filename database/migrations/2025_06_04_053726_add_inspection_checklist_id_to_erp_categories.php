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
        Schema::table('erp_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('inspection_checklist_id')->nullable()->after('organization_id');
        });
        Schema::table('erp_items', function (Blueprint $table) {
            $table->unsignedBigInteger('inspection_checklist_id')->nullable()->after('is_inspection');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_categories', function (Blueprint $table) {
            $table->dropColumn('inspection_checklist_id');
        });
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropColumn('inspection_checklist_id');
        });
    }
};
