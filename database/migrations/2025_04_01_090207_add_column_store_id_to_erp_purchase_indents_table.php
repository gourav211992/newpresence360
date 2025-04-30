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
        Schema::table('erp_purchase_indents', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('department_id');
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_id');
        });
        Schema::table('erp_purchase_indents_history', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('department_id');
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_purchase_indents_history', function (Blueprint $table) {
            $table->dropColumn(['store_id','sub_store_id']);
        });
        Schema::table('erp_purchase_indents', function (Blueprint $table) {
            $table->dropColumn(['store_id','sub_store_id']);
        });
    }
};
