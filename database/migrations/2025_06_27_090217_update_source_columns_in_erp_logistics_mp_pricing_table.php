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
        Schema::table('erp_logistics_mp_pricing', function (Blueprint $table) {
             $table->dropColumn(['source_state_id', 'source_city_id']);
            $table->unsignedBigInteger('source_route_id')->nullable()->after('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_logistics_mp_pricing', function (Blueprint $table) {
             $table->unsignedBigInteger('source_state_id')->nullable(); 
            $table->unsignedBigInteger('source_city_id')->nullable();
            $table->dropColumn('source_route_id')->nullable();
        });
    }
};
