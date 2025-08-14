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
        Schema::table('erp_addresses', function (Blueprint $table) {
            $table->bigInteger('pincode_master_id')->unsigned()->nullable()->after('city_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_addresses', function (Blueprint $table) {
            $table->dropColumn('pincode_master_id');
        });
    }
};
