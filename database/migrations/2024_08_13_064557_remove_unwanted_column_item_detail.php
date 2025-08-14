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
        if (!Schema::hasColumn('erp_item_details', 'group_id')) {
            return;
        }

        Schema::table('erp_item_details', function (Blueprint $table) {
            $table->dropColumn('group_id');
            $table->dropColumn('company_id');
            $table->dropColumn('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_item_details', function (Blueprint $table) {
            //
        });
    }
};
