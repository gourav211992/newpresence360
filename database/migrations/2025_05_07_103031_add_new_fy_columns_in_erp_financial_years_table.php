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
        Schema::table('erp_financial_years', function (Blueprint $table) {
            //
            $table->enum('fy_status', ['current', 'next', 'prev'])->default('current')->nullable()->after('status');
            $table->boolean('fy_close')->default(false)->nullable();
            $table->boolean('lock_fy')->default(false)->nullable();
            $table->json('access_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_financial_years', function (Blueprint $table) {
            //
            $table->dropColumn('fy_status');
        });
    }
};
