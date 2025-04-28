<?php

use App\Helpers\ConstantHelper;
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
        Schema::table('erp_sale_orders', function (Blueprint $table) {
            $table->enum('doc_number_type', ConstantHelper::DOC_NO_TYPES) -> default(ConstantHelper::DOC_NO_TYPE_MANUAL) ->after('document_number');
            $table->enum('doc_reset_pattern', ConstantHelper::DOC_RESET_PATTERNS) -> nullable() -> default(NULL) ->after('doc_number_type');
            $table->string('doc_prefix') -> nullable() ->after('doc_reset_pattern');
            $table->string('doc_suffix') -> nullable() ->after('doc_prefix');
            $table->integer('doc_no') -> nullable() ->after('doc_suffix');
        });

        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->enum('doc_number_type', ConstantHelper::DOC_NO_TYPES) -> default(ConstantHelper::DOC_NO_TYPE_MANUAL) ->after('document_number');
            $table->enum('doc_reset_pattern', ConstantHelper::DOC_RESET_PATTERNS) -> nullable() -> default(NULL) ->after('doc_number_type');
            $table->string('doc_prefix') -> nullable() ->after('doc_reset_pattern');
            $table->string('doc_suffix') -> nullable() ->after('doc_prefix');
            $table->integer('doc_no') -> nullable() ->after('doc_suffix');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_module', function (Blueprint $table) {
            Schema::table('erp_sale_orders', function (Blueprint $table) {
                $table->dropColumn(['doc_no', 'doc_suffix', 'doc_prefix', 'doc_reset_pattern', 'doc_number_type']);
            });
    
            Schema::table('erp_sale_invoices', function (Blueprint $table) {
                $table->dropColumn(['doc_no', 'doc_suffix', 'doc_prefix', 'doc_reset_pattern', 'doc_number_type']);
            });
        });
    }
};
