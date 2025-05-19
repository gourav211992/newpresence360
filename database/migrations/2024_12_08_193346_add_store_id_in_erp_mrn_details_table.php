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
        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('hsn_code');
            $table->string('store_code', 191)->nullable()->after('store_id');
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('hsn_code');
            $table->string('store_code', 191)->nullable()->after('store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->dropColumn('store_id');
            $table->dropColumn('store_code');
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->dropColumn('store_id');
            $table->dropColumn('store_code');
        });
    }
};
