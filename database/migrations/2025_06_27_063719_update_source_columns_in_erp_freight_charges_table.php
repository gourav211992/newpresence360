<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('erp_freight_charges', function (Blueprint $table) {
            // Add new route columns
            $table->unsignedBigInteger('source_route_id')->nullable()->after('source_state_id');
            $table->unsignedBigInteger('destination_route_id')->nullable()->after('destination_state_id');
        });



        // Drop old columns
        Schema::table('erp_freight_charges', function (Blueprint $table) {
            $table->dropColumn('source_state_id');
            $table->dropColumn('destination_state_id');
            $table->dropColumn('source_city_id');
            $table->dropColumn('destination_city_id');
        });
    }

    public function down(): void
    {
        Schema::table('erp_freight_charges', function (Blueprint $table) {
            $table->unsignedBigInteger('source_state_id')->nullable()->after('source_route_id');
            $table->unsignedBigInteger('destination_state_id')->nullable()->after('destination_route_id');
        });

        // Copy data back
        DB::statement('UPDATE erp_freight_charges SET source_state_id = source_route_id');
        DB::statement('UPDATE erp_freight_charges SET destination_state_id = destination_route_id');

        Schema::table('erp_freight_charges', function (Blueprint $table) {
            $table->dropColumn('source_route_id');
            $table->dropColumn('destination_route_id');
            $table->unsignedBigInteger('source_city_id')->nullable();
            $table->unsignedBigInteger('destination_city_id')->nullable();
        });
    }
};

