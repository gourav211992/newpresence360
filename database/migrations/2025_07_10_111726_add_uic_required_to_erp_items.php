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
            $table->enum('uic_required', [0, 1])->default(0)->after('uom_id') -> index();
        });
        Schema::table('erp_items_history', function (Blueprint $table) {
            $table->enum('uic_required', [0, 1])->default(0)->after('uom_id') -> index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropColumn(['uic_required']);
        });
        Schema::table('erp_items_history', function (Blueprint $table) {
            $table->dropColumn(['uic_required']);
        });
    }
};
