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
        Schema::table('erp_vouchers_history', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('location')->nullable()->after('revision_date');
            $table->foreign('location')->references('id')->on('erp_stores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_vouchers_history', function (Blueprint $table) {
            //
            $table->dropForeign(['location']);
            $table->dropColumn('location');
        });
    }
};
