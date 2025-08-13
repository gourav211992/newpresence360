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
        Schema::table('erp_pl_headers', function (Blueprint $table) {
            $table->unsignedBigInteger('staging_sub_store_id') -> after('sub_store_id') -> nullable();
            $table->string('staging_sub_store_code') -> after('staging_sub_store_id') -> nullable();
            
        });
        Schema::table('erp_pl_headers_history', function (Blueprint $table) {
            $table->unsignedBigInteger('staging_sub_store_id') -> after('sub_store_id') -> nullable();
            $table->string('staging_sub_store_code') -> after('staging_sub_store_id') -> nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pl_headers', function (Blueprint $table) {
            $table->dropColumn(['staging_sub_store_id', 'staging_sub_store_code']);

        });
        Schema::table('erp_pl_headers_history', function (Blueprint $table) {
            $table->dropColumn(['staging_sub_store_id', 'staging_sub_store_code']);
        });
    }
};
