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
    public function up()
    {
        Schema::create('erp_finance_fixed_asset_registration', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id');
            $table->string('document_number', 255);
            $table->date('document_date')->nullable();
            $table->enum('doc_number_type', ConstantHelper::DOC_NO_TYPES)->default(ConstantHelper::DOC_NO_TYPE_MANUAL);
            $table->enum('doc_reset_pattern', ConstantHelper::DOC_RESET_PATTERNS)->nullable()->default(null);
            $table->string('doc_prefix')->nullable();
            $table->string('doc_suffix')->nullable();
            $table->integer('doc_no')->nullable();

            // New fields
            $table->string('reference_no')->nullable();
            $table->string('status', 50)->default('active');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('asset_name', 255)->nullable();
            $table->string('asset_code', 100)->nullable();
            $table->integer('quantity')->nullable();
            $table->unsignedBigInteger('ledger_id')->nullable();
            $table->unsignedBigInteger('ledger_group_id')->nullable();
            $table->unsignedBigInteger('mrn_header_id')->nullable();
            $table->date('capitalize_date')->nullable();
            $table->string('maintenance_schedule', 50)->nullable();
            $table->string('depreciation_method', 50)->nullable();
            $table->integer('useful_life')->nullable();
            $table->decimal('salvage_value', 15, 2)->nullable();
            $table->decimal('depreciation_percentage', 5, 2)->nullable();
            $table->decimal('total_depreciation', 15, 2)->nullable();
            $table->decimal('current_value', 15, 2)->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('supplier_invoice_no', 100)->nullable();
            $table->date('supplier_invoice_date')->nullable();
            $table->decimal('sub_total', 15, 2)->nullable();
            $table->decimal('tax', 15, 2)->nullable();
            $table->decimal('purchase_amount', 15, 2)->nullable();
            $table->date('book_date')->nullable();

            // Existing fields
            $table->string('document_status', 50);
            $table->integer('approval_level')->default(0);
            $table->string('revision_number')->nullable();
            $table->date('revision_date')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->string('type', 100);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('erp_finance_fixed_asset_registration');
    }
};
