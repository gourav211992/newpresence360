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
        if (Schema::hasTable('erp_jo_items') && !Schema::hasColumn('erp_jo_items', 'service_item_id')) {
            Schema::table('erp_jo_items', function (Blueprint $table) {
                $table->unsignedBigInteger('service_item_id')->nullable()->after('item_id');
            });
        }
        if (Schema::hasTable('erp_jo_products') && !Schema::hasColumn('erp_jo_products', 'service_item_id')) {
            Schema::table('erp_jo_products', function (Blueprint $table) {
                $table->unsignedBigInteger('service_item_id')->nullable()->after('item_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('erp_jo_items') && Schema::hasColumn('erp_jo_items', 'service_item_id')) {
            Schema::table('erp_jo_items', function (Blueprint $table) {
                $table->dropColumn('service_item_id');
            });
        }
        if (Schema::hasTable('erp_jo_products') && Schema::hasColumn('erp_jo_products', 'service_item_id')) {
            Schema::table('erp_jo_products', function (Blueprint $table) {
                $table->dropColumn('service_item_id');
            });
        }
    }
};
