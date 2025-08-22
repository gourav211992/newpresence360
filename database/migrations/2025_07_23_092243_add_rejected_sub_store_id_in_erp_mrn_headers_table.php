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
        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->bigInteger('rejected_sub_store_id')->nullable()->after('sub_store_id');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->bigInteger('rejected_sub_store_id')->nullable()->after('sub_store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->dropColumn('rejected_sub_store_id');
        });

        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->dropColumn('rejected_sub_store_id');
        });
    }
};
