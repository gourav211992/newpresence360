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
        Schema::table('erp_item_details', function (Blueprint $table) {
            $table->date('due_date')->after('date')->nullable()->default(null);
        });
        Schema::table('erp_item_details_history', function (Blueprint $table) {
            $table->date('due_date')->after('date')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_item_details', function (Blueprint $table) {
            $table->dropColoumn('due_date');
        });
        Schema::table('erp_item_details_history', function (Blueprint $table) {
            $table->dropColoumn('due_date');
        });
    }
};
