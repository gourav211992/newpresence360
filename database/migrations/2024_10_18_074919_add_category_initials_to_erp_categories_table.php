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
        Schema::table('erp_categories', function (Blueprint $table) {
            $table->string('cat_initials', 10)->nullable()->index()->after('name'); 
            $table->string('sub_cat_initials', 10)->nullable()->index()->after('cat_initials');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_categories', function (Blueprint $table) {
            $table->dropColumn('cat_initials'); 
            $table->dropColumn('sub_cat_initials');
        });
    }
};
