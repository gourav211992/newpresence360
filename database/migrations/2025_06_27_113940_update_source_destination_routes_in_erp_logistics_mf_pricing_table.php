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
        Schema::table('erp_logistics_mf_pricing', function (Blueprint $table) {
              $table->dropColumn([
                'source_city_id',
                'source_state_id',
                'destination_state_id',
                'destination_city_id',
            ]);

            // Add new columns
            $table->unsignedBigInteger('source_route_id')->nullable()->after('company_id');
            $table->unsignedBigInteger('destination_route_id')->nullable()->after('source_route_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_logistics_mf_pricing', function (Blueprint $table) {
            $table->unsignedBigInteger('source_city_id')->nullable();
            $table->unsignedBigInteger('source_state_id')->nullable();
            $table->unsignedBigInteger('destination_state_id')->nullable();
            $table->unsignedBigInteger('destination_city_id')->nullable();

            // Drop newly added columns
            $table->dropColumn(['source_route_id', 'destination_route_id']);
        });
    }
};
