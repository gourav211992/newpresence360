<?php

use App\Helpers\ConstantHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'erp_purchase_orders',
        'erp_purchase_orders_history',
        'erp_purchase_indents',
        'erp_purchase_indents_history',
        'erp_boms',
        'erp_boms_history'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->enum('doc_number_type', ConstantHelper::DOC_NO_TYPES)
                    ->default(ConstantHelper::DOC_NO_TYPE_MANUAL)
                    ->after('document_number');
                    
                $table->enum('doc_reset_pattern', ConstantHelper::DOC_RESET_PATTERNS)
                    ->nullable()
                    ->default(null)
                    ->after('doc_number_type');
                    
                $table->string('doc_prefix')->nullable()->after('doc_reset_pattern');
                $table->string('doc_suffix')->nullable()->after('doc_prefix');
                $table->integer('doc_no')->nullable()->after('doc_suffix');
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
                    'doc_number_type',
                    'doc_reset_pattern',
                    'doc_prefix',
                    'doc_suffix',
                    'doc_no'
                ]);
            });
        }
    }
};
