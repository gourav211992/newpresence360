<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Update erp_equip_maintenance_details
        Schema::table('erp_equip_maintenance_details', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_equip_maintenance_details', 'start_date')) {
                $table->date('start_date')->nullable()->after('maintenance_type_id');
            }

            if (!Schema::hasColumn('erp_equip_maintenance_details', 'maintenance_bom_id')) {
                $table->unsignedBigInteger('maintenance_bom_id')->nullable()->after('start_date');
            }
        });

        // Update erp_equip_maintenance_detail_histories
        Schema::table('erp_equip_maintenance_detail_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_equip_maintenance_detail_histories', 'start_date')) {
                $table->date('start_date')->nullable()->after('maintenance_type_id');
            }

            if (!Schema::hasColumn('erp_equip_maintenance_detail_histories', 'maintenance_bom_id')) {
                $table->unsignedBigInteger('maintenance_bom_id')->nullable()->after('start_date');
            }
        });
    }

    public function down(): void
    {
        // Rollback for erp_equip_maintenance_details
        Schema::table('erp_equip_maintenance_details', function (Blueprint $table) {
            if (Schema::hasColumn('erp_equip_maintenance_details', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('erp_equip_maintenance_details', 'maintenance_bom_id')) {
                $table->dropColumn('maintenance_bom_id');
            }
        });

        // Rollback for erp_equip_maintenance_detail_histories
        Schema::table('erp_equip_maintenance_detail_histories', function (Blueprint $table) {
            if (Schema::hasColumn('erp_equip_maintenance_detail_histories', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('erp_equip_maintenance_detail_histories', 'maintenance_bom_id')) {
                $table->dropColumn('maintenance_bom_id');
            }
        });
    }
};
