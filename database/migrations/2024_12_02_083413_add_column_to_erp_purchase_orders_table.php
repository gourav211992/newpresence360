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
        Schema::table('erp_purchase_orders', function (Blueprint $table) {
            $table->string('type')->default('po')->after('id');
        });
        Schema::table('erp_purchase_orders_history', function (Blueprint $table) {
            $table->string('type')->default('po')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_purchase_orders_history', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('erp_purchase_orders', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
