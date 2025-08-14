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
            $table->string('so_tracking_required')->default('no')->after('doc_no');
        });
        Schema::table('erp_purchase_indents_history', function (Blueprint $table) {
            $table->string('so_tracking_required')->default('no')->after('doc_no');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_purchase_indents_history', function (Blueprint $table) {
            $table->dropColumn('so_tracking_required');
        });
        Schema::table('erp_purchase_indents', function (Blueprint $table) {
            $table->dropColumn('so_tracking_required');
        });
    }
};
