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
        Schema::table('erp_jo_items', function (Blueprint $table) {
            $table->double('mi_qty', 20, 6) -> default(0) -> after('consumed_qty');
        });
        Schema::table('erp_jo_products', function (Blueprint $table) {
            $table->double('mi_qty', 20, 6) -> default(0) -> after('short_close_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_jo_items', function (Blueprint $table) {
            $table->dropColumn(['mi_qty']);
        });
        Schema::table('erp_jo_products', function (Blueprint $table) {
            $table->dropColumn(['mi_qty']);
        });
    }
};
