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
        Schema::table('erp_pl_item_details', function (Blueprint $table) {
            $table->unsignedInteger('pl_item_id')->nullable() -> default(null)->after('id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pl_item_details', function (Blueprint $table) {
            $table->dropColumn(['pl_item_id']);
        });
    }
};
