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
        // For erp_bom_overheads_history
        Schema::table('erp_bom_overheads_history', function (Blueprint $table) {
            if (Schema::hasColumn('erp_bom_overheads_history', 'level')) {
                $table->dropColumn('level');
            }
            if (Schema::hasColumn('erp_bom_overheads_history', 'overhead_id')) {
                $table->dropColumn('overhead_id');
            }
            if (Schema::hasColumn('erp_bom_overheads_history', 'overhead_perc')) {
                $table->dropColumn('overhead_perc');
            }
            if (Schema::hasColumn('erp_bom_overheads_history', 'ledger_id')) {
                $table->dropColumn('ledger_id');
            }
            if (Schema::hasColumn('erp_bom_overheads_history', 'ledger_group_id')) {
                $table->dropColumn('ledger_group_id');
            }
        });

        // For erp_bom_overheads
        Schema::table('erp_bom_overheads', function (Blueprint $table) {
            if (Schema::hasColumn('erp_bom_overheads', 'level')) {
                $table->dropColumn('level');
            }
            if (Schema::hasColumn('erp_bom_overheads', 'overhead_id')) {
                $table->dropColumn('overhead_id');
            }
            if (Schema::hasColumn('erp_bom_overheads', 'overhead_perc')) {
                $table->dropColumn('overhead_perc');
            }
            if (Schema::hasColumn('erp_bom_overheads', 'ledger_id')) {
                $table->dropColumn('ledger_id');
            }
            if (Schema::hasColumn('erp_bom_overheads', 'ledger_group_id')) {
                $table->dropColumn('ledger_group_id');
            }
        });

        Schema::table('erp_bom_overheads', function (Blueprint $table) {
            $table->integer('level')->default(1)->after('type');
            $table->unsignedBigInteger('overhead_id')->after('level');
            $table->double('overhead_perc', 20, 6)->nullable()->after('overhead_description');
            $table->unsignedBigInteger('ledger_id')->nullable()->after('overhead_amount');
            $table->unsignedBigInteger('ledger_group_id')->nullable()->after('ledger_id');
        });
        Schema::table('erp_bom_overheads_history', function (Blueprint $table) {
            $table->integer('level')->default(1)->after('type');
            $table->unsignedBigInteger('overhead_id')->after('level');
            $table->double('overhead_perc', 20, 6)->nullable()->after('overhead_description');
            $table->unsignedBigInteger('ledger_id')->nullable()->after('overhead_amount');
            $table->unsignedBigInteger('ledger_group_id')->nullable()->after('ledger_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For erp_bom_overheads_history
        Schema::table('erp_bom_overheads_history', function (Blueprint $table) {
            if (Schema::hasColumn('erp_bom_overheads_history', 'level')) {
                $table->dropColumn('level');
            }
            if (Schema::hasColumn('erp_bom_overheads_history', 'overhead_id')) {
                $table->dropColumn('overhead_id');
            }
            if (Schema::hasColumn('erp_bom_overheads_history', 'overhead_perc')) {
                $table->dropColumn('overhead_perc');
            }
            if (Schema::hasColumn('erp_bom_overheads_history', 'ledger_id')) {
                $table->dropColumn('ledger_id');
            }
            if (Schema::hasColumn('erp_bom_overheads_history', 'ledger_group_id')) {
                $table->dropColumn('ledger_group_id');
            }
        });

        // For erp_bom_overheads
        Schema::table('erp_bom_overheads', function (Blueprint $table) {
            if (Schema::hasColumn('erp_bom_overheads', 'level')) {
                $table->dropColumn('level');
            }
            if (Schema::hasColumn('erp_bom_overheads', 'overhead_id')) {
                $table->dropColumn('overhead_id');
            }
            if (Schema::hasColumn('erp_bom_overheads', 'overhead_perc')) {
                $table->dropColumn('overhead_perc');
            }
            if (Schema::hasColumn('erp_bom_overheads', 'ledger_id')) {
                $table->dropColumn('ledger_id');
            }
            if (Schema::hasColumn('erp_bom_overheads', 'ledger_group_id')) {
                $table->dropColumn('ledger_group_id');
            }
        });
    }
};
