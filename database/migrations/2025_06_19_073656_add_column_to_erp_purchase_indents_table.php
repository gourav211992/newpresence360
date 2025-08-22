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
        Schema::table('erp_purchase_indents', function (Blueprint $table) {
            $table->string('procurement_type',10)->default('rm')->after('sub_store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_purchase_indents', function (Blueprint $table) {
            $table->dropColumn('procurement_type');
        });
    }
};
