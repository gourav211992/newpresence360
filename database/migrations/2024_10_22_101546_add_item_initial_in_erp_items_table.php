<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('erp_items', function (Blueprint $table) {
            $table->string('item_initial')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropColumn('item_initial');
        });
    }
};
