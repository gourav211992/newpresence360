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
        Schema::table('erp_mi_items', function (Blueprint $table) {
            $table->unsignedBigInteger('from_store_id')->nullable()->after('uom_code');
            $table->string('from_store_code')->nullable()->after('from_store_id');
            $table->unsignedBigInteger('to_store_id')->nullable()->after('from_store_code');
            $table->string('to_store_code')->nullable()->after('to_store_id');
        });
        Schema::table('erp_mi_items_history', function (Blueprint $table) {
            $table->unsignedBigInteger('from_store_id')->nullable()->after('uom_code');
            $table->string('from_store_code')->nullable()->after('from_store_id');
            $table->unsignedBigInteger('to_store_id')->nullable()->after('from_store_code');
            $table->string('to_store_code')->nullable()->after('to_store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mi_items', function (Blueprint $table) {
            $table->dropColumn(['from_store_id', 'from_store_code', 'to_store_id', 'to_store_code']);
        });
        Schema::table('erp_mi_items_history', function (Blueprint $table) {
            $table->dropColumn(['from_store_id', 'from_store_code', 'to_store_id', 'to_store_code']);
        });
    }
};
