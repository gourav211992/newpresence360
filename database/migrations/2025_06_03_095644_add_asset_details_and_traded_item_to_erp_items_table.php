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
        Schema::table('erp_items', function (Blueprint $table) {
            $table->boolean('is_traded_item')->default(0);
            $table->boolean('is_asset')->default(0)->after('is_traded_item');
            $table->unsignedBigInteger('asset_category_id')->nullable()->after('is_asset');
            $table->integer('expected_life')->nullable()->after('asset_category_id');
            $table->string('maintenance_schedule', 255)->nullable()->after('expected_life');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropColumn('is_traded_item');
            $table->dropColumn('is_asset');
            $table->dropColumn('asset_category_id');
            $table->dropColumn('expected_life');
            $table->dropColumn('maintenance_schedule');
        });
    }
};
