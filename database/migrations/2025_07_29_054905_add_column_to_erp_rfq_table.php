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
        Schema::table('erp_rfq_headers', function (Blueprint $table) {
            $table->unsignedBigInteger('selected_pq')->nullable()->after('company_id');
            $table->unsignedBigInteger('selected_vendor')->nullable()->after('selected_pq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_rfq_headers', function (Blueprint $table) {
            $table->dropColumn(['selected_pq', 'selected_vendor']);
        });
    }
};
