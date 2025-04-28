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
        Schema::table('erp_bom_attributes', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id')->after('bom_detail_id')->comment('erp_items id');
        });
        Schema::table('erp_bom_attributes_history', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id')->after('bom_detail_id')->comment('erp_items id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_bom_attributes', function (Blueprint $table) {
            $table->dropColumn('item_id');
        });
        Schema::table('erp_bom_attributes_history', function (Blueprint $table) {
            $table->dropColumn('item_id');
        });
    }
};
