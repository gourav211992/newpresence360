<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('erp_finance_fixed_asset_depreciation_history', function (Blueprint $table) {
            $table->id();

            // History tracking reference
            $table->unsignedBigInteger('source_id')->index()->comment('Refers to original erp_finance_fixed_asset_depreciation.id');

            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id');
            $table->string('document_number');
            $table->date('document_date')->nullable();
            $table->enum('doc_number_type', ['Auto', 'Manually'])->default('Manually');
            $table->enum('doc_reset_pattern', ['Never', 'Yearly', 'Quarterly', 'Monthly'])->nullable();
            $table->string('doc_prefix')->nullable();
            $table->string('doc_suffix')->nullable();
            $table->integer('doc_no')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('period');
            $table->json('assets');
            $table->json('asset_details')->nullable();
            $table->decimal('grand_total_current_value', 15, 2)->default(0.00);
            $table->decimal('grand_total_current_value_after_dep', 20, 2)->nullable();
            $table->decimal('grand_total_dep_amount', 15, 2)->default(0.00);
            $table->decimal('grand_total_after_dep_value', 15, 2)->default(0.00);
            $table->string('document_status');
            $table->integer('approval_level')->default(1);
            $table->integer('revision_number')->default(0);
            $table->date('revision_date')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->string('type');
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_finance_fixed_asset_depreciation_history');
    }
};
