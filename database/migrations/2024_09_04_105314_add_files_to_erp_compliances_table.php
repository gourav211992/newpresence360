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
        Schema::table('erp_compliances', function (Blueprint $table) {
            $table->json('gst_certificate')->nullable()->after('gstin_no');
            $table->json('msme_certificate')->nullable()->after('msme_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_compliances', function (Blueprint $table) {
            $table->dropColumn('gst_certificate');
            $table->dropColumn('msme_certificate');
        });
    }
};
