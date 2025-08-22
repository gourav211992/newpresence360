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
        Schema::table('erp_mrn_jo_items', function (Blueprint $table) {
            $table->unsignedBigInteger('so_id')->nullable()->after('sub_store_id');
            $table->unsignedBigInteger('item_id')->nullable()->after('so_id');
            $table->string('item_code')->nullable()->after('item_id');
            $table->unsignedBigInteger('uom_id')->nullable()->after('item_code');
            $table->json('attributes')->nullable()->after('uom_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_jo_items', function (Blueprint $table) {
            $table->dropColumn('attributes');
            $table->dropColumn('uom_id');
            $table->dropColumn('item_code');
            $table->dropColumn('item_id');
            $table->dropColumn('so_id');
        });
    }
};
