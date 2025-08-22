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
            $table->string('brand_name')->nullable()->after('maintenance_schedule');
            $table->string('model_no')->nullable()->after('brand_name');
        });

        Schema::table('erp_items_history', function (Blueprint $table) {
            $table->string('brand_name')->nullable()->after('maintenance_schedule');
            $table->string('model_no')->nullable()->after('brand_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropColumn(['brand_name', 'model_no']);
        });

        Schema::table('erp_items_history', function (Blueprint $table) {
            $table->dropColumn(['brand_name', 'model_no']);
        });
    }
};
