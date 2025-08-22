<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'erp_purchase_indents',
            'erp_purchase_orders',
            'erp_gate_entry_headers',
            'erp_mrn_headers',
            'erp_purchase_return_headers',
            'erp_pb_headers',
            'erp_expense_headers',
            'erp_material_issue_header',
            'erp_material_return_header',
            'erp_boms',
            'erp_production_work_orders',
            'erp_mfg_orders',
            'erp_production_slips',
            'erp_sale_orders',
            'erp_sale_invoices',
            'erp_sale_returns',
        ];

        foreach ($tables as $table) {
            // Alter table collation
            DB::statement("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
        // Add Column user_name and department_code in Material Issue Header Table
        Schema::table('erp_material_issue_header', function ($table) {
            if (!Schema::hasColumn('erp_material_issue_header', 'user_name')) {
                $table->string('user_name')->nullable();
            }
            if (!Schema::hasColumn('erp_material_issue_header', 'department_code')) {
                $table->string('department_code')->nullable();
            }
        });
        // Add same column in erp__mi_items table
        Schema::table('erp_mi_items', function ($table) {
            if (!Schema::hasColumn('erp_mi_items', 'user_name')) {
                $table->string('user_name')->nullable();
            }
            if (!Schema::hasColumn('erp_mi_items', 'department_code')) {
                $table->string('department_code')->nullable();
            }
        });
        // make changes in history table
        Schema::table('erp_material_issue_header_history', function ($table) {
            if(!Schema::hasColumn('erp_material_issue_header_history', 'user_name')) {
                $table->string('user_name')->nullable();
            }
            if(!Schema::hasColumn('erp_material_issue_header_history', 'department_code')) {
                $table->string('department_code')->nullable();
            }
        });
        // make changes in erp__mi_items_history table
        Schema::table('erp_mi_items_history', function ($table) {
            if(!Schema::hasColumn('erp_mi_items_history', 'user_name')) {
                $table->string('user_name')->nullable();
            }
            if(!Schema::hasColumn('erp_mi_items_history', 'department_code')) {
                $table->string('department_code')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'erp_purchase_indents',
            'erp_purchase_orders',
            'erp_gate_entry_headers',
            'erp_mrn_headers',
            'erp_purchase_return_headers',
            'erp_pb_headers',
            'erp_expense_headers',
            'erp_material_issue_header',
            'erp_material_return_header',
            'erp_boms',
            'erp_production_work_orders',
            'erp_mfg_orders',
            'erp_production_slips',
            'erp_sale_orders',
            'erp_sale_invoices',
            'erp_sale_returns',
        ];

        foreach ($tables as $table) {
            // Revert table collation
            DB::statement("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        }
        // Remove Column user_name and department_code in Material Issue Header Table
        Schema::table('erp_material_issue_header', function ($table) {
            if(Schema::hasColumn('erp_material_issue_header', 'user_name')) {
                $table->dropColumn('user_name');
            }
            if(Schema::hasColumn('erp_material_issue_header', 'department_code')) {
                $table->dropColumn('department_code');
            }
        });
        // Remove same column in erp__mi_items table
        Schema::table('erp_mi_items', function ($table) {
            if(Schema::hasColumn('erp_mi_items', 'user_name')) {
                $table->dropColumn('user_name');
            }
            if(Schema::hasColumn('erp_mi_items', 'department_code')) {
                $table->dropColumn('department_code');
            }
        });
        // make changes in history table
        Schema::table('erp_material_issue_header_history', function ($table) {
            if(Schema::hasColumn('erp_material_issue_header_history', 'user_name')) {
                $table->dropColumn('user_name');
            }
            if(Schema::hasColumn('erp_material_issue_header_history', 'department_code')) {
                $table->dropColumn('department_code');
            }
        });
        // make changes in erp__mi_items_history table
        Schema::table('erp_mi_items_history', function ($table) {
            if(Schema::hasColumn('erp_mi_items_history', 'user_name')) {
                $table->dropColumn('user_name');
            }
            if(Schema::hasColumn('erp_mi_items_history', 'department_code')) {
                $table->dropColumn('department_code');
            }
        });
    }
};