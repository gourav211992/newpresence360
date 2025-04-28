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
            $table->unsignedBigInteger('hsn_id')->nullable()->index();
            $table->foreign('hsn_id')->references('id')->on('erp_hsns')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_categories', function (Blueprint $table) {
            $table->dropForeign(['hsn_id']); 
            $table->dropColumn('hsn_id'); 
        });
    }
};
