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
        Schema::table('erp_packing_lists', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_store_id')->after('store_id');
        });
        Schema::table('erp_packing_lists_history', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_store_id')->after('store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_packing_lists', function (Blueprint $table) {
            $table->dropColumn(['sub_store_id']);
        });
        Schema::table('erp_packing_lists_history', function (Blueprint $table) {
            $table->dropColumn(['sub_store_id']);
        });
    }
};
