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
        Schema::table('erp_vendor_items', function (Blueprint $table) {
            $table->string('vendor_code')->nullable()->after('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_vendor_items', function (Blueprint $table) {
            $table->dropColumn('vendor_code');
        });
    }
};
