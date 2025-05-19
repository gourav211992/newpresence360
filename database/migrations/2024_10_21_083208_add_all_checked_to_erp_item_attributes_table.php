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
        Schema::table('erp_item_attributes', function (Blueprint $table) {
            $table->boolean('all_checked')->default(false); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_item_attributes', function (Blueprint $table) {
            $table->dropColumn('all_checked'); 
        });
    }
};
