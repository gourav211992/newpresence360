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
        Schema::table('erp_home_loans', function (Blueprint $table) {
            $table->softDeletes()->after('type');
            $table->string('concern_name')->nullable()->after('type');
            $table->string('promoter_name')->nullable()->after('concern_name');
            $table->string('activity_line')->nullable()->after('promoter_name');
            $table->string('name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_home_loans', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['concern_name', 'promoter_name', 'activity_line']);
            DB::statement('ALTER TABLE `erp_home_loans` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
        });
    }
};
