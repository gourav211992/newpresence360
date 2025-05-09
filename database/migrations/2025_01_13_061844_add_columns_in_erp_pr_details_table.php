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
        Schema::table('erp_pr_details', function (Blueprint $table) {
            $table->longText('item_attributes')->after('item_id')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pr_details', function (Blueprint $table) {
            $table->dropColumn('item_attributes');

        });
    }
};
