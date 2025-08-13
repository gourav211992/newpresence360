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
        Schema::table('erp_pq_headers', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('rfq_id')->nullable()->after('id');
            $table->unsignedBigInteger('selected_pq')->nullable()->after('rfq_id');
        });
        Schema::table('erp_pq_items', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('rfq_id')->nullable()->after('pq_header_id');
        });
        Schema::table('erp_pq_headers_history', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('rfq_id')->nullable()->after('id');
            $table->unsignedBigInteger('selected_pq')->nullable()->after('rfq_id');
        });
        Schema::table('erp_pq_items_history', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('rfq_id')->nullable()->after('pq_header_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pq_tables', function (Blueprint $table) {
            //
        });
    }
};
