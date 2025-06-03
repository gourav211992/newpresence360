<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('erp_finance_fixed_asset_split_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('currency_id');
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

           
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('sub_asset_id');
            $table->json('sub_assets'); 
            $table->unsignedBigInteger('category_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->unsignedBigInteger('ledger_id')->nullable();
            $table->unsignedBigInteger('ledger_group_id')->nullable();
            $table->date('capitalize_date')->nullable();
            $table->string('maintenance_schedule', 50)->nullable();
            $table->string('depreciation_method', 50)->nullable();
            $table->integer('useful_life')->nullable();
            $table->decimal('salvage_value', 15, 2)->nullable();
            $table->decimal('depreciation_percentage', 5, 2)->nullable();
            $table->decimal('depreciation_percentage_year', 8, 2)->nullable();
            $table->decimal('total_depreciation', 15, 2)->nullable();
            $table->decimal('current_value', 15, 2)->nullable();
            $table->decimal('current_value_after_dep', 15, 2)->nullable();
            
            
            $table->string('document_status', 50);
            $table->integer('approval_level')->default(1);
            $table->integer('revision_number')->default(0);
            $table->date('revision_date')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->string('type', 100);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_finance_fixed_asset_split_history');
    }
};
