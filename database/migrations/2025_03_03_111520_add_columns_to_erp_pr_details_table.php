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
        Schema::table('erp_pr_details', function (Blueprint $table) {
            $table->integer('item_id')->nullable()->after('consumption');
            $table->enum('qa', ['yes', 'no'])->default('no')->after('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pr_details', function (Blueprint $table) {
            $table->dropColumn('item_id');
            $table->dropColumn('qa');
        });
    }
};
