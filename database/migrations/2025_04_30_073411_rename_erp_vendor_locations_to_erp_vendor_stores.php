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
        Schema::table('erp_vendor_locations', function (Blueprint $table) {
            $table->dropForeign('erp_vendor_locations_store_id_foreign');
            $table->rename('erp_vendor_stores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_vendor_stores', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('erp_stores');
            $table->rename('erp_vendor_locations');
        });
    }
};
