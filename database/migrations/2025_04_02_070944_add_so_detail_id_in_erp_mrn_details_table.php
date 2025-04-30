<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    private array $tables = [
        'erp_gate_entry_details',
        'erp_gate_entry_details_history',
        'erp_mrn_details',
        'erp_mrn_detail_histories',
        'erp_pb_details',
        'erp_pb_detail_histories',
        'erp_purchase_return_details',
        'erp_purchase_return_details_history',
        'erp_expense_details',
        'erp_expense_detail_histories'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->json('so_item_id')->nullable()->after('item_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn([
                    'so_item_id'
                ]);
            });
        }
    }
};
