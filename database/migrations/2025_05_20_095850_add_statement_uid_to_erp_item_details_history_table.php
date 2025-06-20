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
        Schema::table('erp_item_details_history', function (Blueprint $table) {
            $table->string('statement_uid','200')->nullable()->index()->after('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_item_details_history', function (Blueprint $table) {
            $table->dropColumn('statement_uid');
        });
    }
};
