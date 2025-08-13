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
        Schema::table('erp_vendor_stores', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->after('vendor_id');
            $table->unsignedBigInteger('location_id')->nullable()->after('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
