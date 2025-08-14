<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
    public function up()
    {
        Schema::create('upload_fa_masters', function (Blueprint $table) {
            $table->id();
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
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('cost_center_id')->nullable();
            $table->string('status', 50)->default('active');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('asset_name')->nullable();
            $table->string('asset_code', 100)->nullable();
            $table->integer('quantity')->nullable();
            $table->unsignedBigInteger('ledger_id')->nullable();
            $table->unsignedBigInteger('ledger_group_id')->nullable();
            $table->date('capitalize_date')->nullable();
            $table->date('last_dep_date')->nullable();
            $table->string('maintenance_schedule', 50)->nullable();
            $table->string('depreciation_method', 50)->nullable();
            $table->integer('useful_life')->nullable();
            $table->decimal('salvage_value', 15, 2)->nullable();
            $table->decimal('depreciation_percentage', 5, 2)->nullable();
            $table->decimal('depreciation_percentage_year', 8, 2)->nullable();
            $table->string('dep_type')->nullable();
            $table->decimal('total_depreciation', 15, 2)->nullable();
            $table->decimal('current_value', 15, 2)->nullable();
            $table->decimal('current_value_after_dep', 15, 2)->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->decimal('tax', 15, 2)->nullable();
            $table->decimal('purchase_amount', 15, 2)->nullable();
            $table->date('book_date')->nullable();
            $table->string('document_status', 50);
            $table->integer('approval_level')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->string('type', 100);
            $table->text('import_remarks')->nullable();
            $table->string('import_status')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('upload_fa_masters');
    }
};
