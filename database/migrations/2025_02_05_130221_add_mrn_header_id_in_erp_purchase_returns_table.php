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
        Schema::table('erp_purchase_return_headers', function (Blueprint $table) {
            $table->unsignedBigInteger('mrn_header_id')->nullable()->after('vendor_code');
        });

        Schema::table('erp_purchase_return_headers_history', function (Blueprint $table) {
            $table->unsignedBigInteger('mrn_header_id')->nullable()->after('vendor_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_purchase_returns', function (Blueprint $table) {
            $table->dropColumn('mrn_header_id');
        });

        Schema::table('erp_purchase_return_headers_history', function (Blueprint $table) {
            $table->dropColumn('mrn_header_id');
        });
    }
};
