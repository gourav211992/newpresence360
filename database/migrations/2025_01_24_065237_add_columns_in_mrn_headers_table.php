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
        'erp_mrn_headers',
        'erp_mrn_header_histories',
        'erp_pb_headers',
        'erp_pb_header_histories',
        'erp_expense_headers',
        'erp_expense_header_histories',
        'erp_purchase_return_headers',
        'erp_purchase_return_headers_history'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->bigInteger('store_id')->nullable()->after('reference_number');
                $table->bigInteger('department_id')->nullable()->after('store_id');
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
                    'store_id',
                    'department_id'
                ]);
            });
        }
    }
};
