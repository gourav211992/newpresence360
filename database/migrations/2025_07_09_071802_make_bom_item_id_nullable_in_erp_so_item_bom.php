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
        Schema::table('erp_so_item_bom', function (Blueprint $table) {
            $table->unsignedBigInteger('bom_id') -> change() -> nullable();
            $table->unsignedBigInteger('bom_detail_id') -> change() -> nullable();
        });
        Schema::table('erp_so_item_bom_history', function (Blueprint $table) {
            $table->unsignedBigInteger('bom_id') -> change() -> nullable();
            $table->unsignedBigInteger('bom_detail_id') -> change() -> nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_so_item_bom', function (Blueprint $table) {
            $table->unsignedBigInteger('bom_id') -> change() -> nullable(false);
            $table->unsignedBigInteger('bom_detail_id') -> change() -> nullable(false);
        });
        Schema::table('erp_so_item_bom_history', function (Blueprint $table) {
            $table->unsignedBigInteger('bom_id') -> change() -> nullable(false);
            $table->unsignedBigInteger('bom_detail_id') -> change() -> nullable(false);
        });
    }
};
