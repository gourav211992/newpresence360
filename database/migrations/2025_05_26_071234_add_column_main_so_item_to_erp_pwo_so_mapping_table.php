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
        Schema::table('erp_pwo_so_mapping', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('pwo_id');
            $table->boolean('main_so_item')->default(false)->after('store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pwo_so_mapping', function (Blueprint $table) {
            $table->dropColumn('store_id');
            $table->dropColumn('main_so_item');
        });
    }
};
