<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('erp_freight_charges', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_freight_charges', 'no_bundle')) {
                $table->integer('no_bundle')->nullable()->after('vehicle_type_id');
            }
            if (!Schema::hasColumn('erp_freight_charges', 'per_bundle')) {
                $table->decimal('per_bundle', 10, 2)->nullable()->after('no_bundle');
            }
        });
    }

    public function down(): void
    {
        Schema::table('erp_freight_charges', function (Blueprint $table) {
            $table->dropColumn(['no_bundle', 'per_bundle']);
        });
    }
};
