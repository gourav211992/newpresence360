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
        Schema::create('erp_logistics_lorry_receipt_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id')->nullable();

            $table->string('book_code', 255)->nullable();

            $table->string('document_type')->nullable();
            $table->string('document_number', 255)->nullable();

            $table->enum('doc_number_type', ['Auto', 'Manually'])->default('Manually');
            $table->enum('doc_reset_pattern', ['Never', 'Yearly', 'Quarterly', 'Monthly'])->nullable();

            $table->string('doc_prefix', 255)->nullable();
            $table->string('doc_suffix', 255)->nullable();

            $table->integer('doc_no')->nullable();

            $table->date('document_date')->nullable();
             $table->string('revision_number')->nullable()->default('0');
            $table->date('revision_date')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('cost_center_id')->nullable();
            $table->unsignedBigInteger('origin_id')->nullable();
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->unsignedBigInteger('consignor_id')->nullable();
            $table->unsignedBigInteger('consignee_id')->nullable();
            $table->unsignedBigInteger('vehicle_type_id')->nullable();
            $table->decimal('distance', 8, 2)->nullable(); 
            $table->decimal('freight_charges', 10, 2)->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->decimal('driver_cash', 10, 2)->nullable();
            $table->decimal('fuel_price', 10, 2)->nullable();
            $table->string('invoice_no')->nullable();
            $table->decimal('invoice_value', 15, 2)->nullable();
            $table->integer('no_of_bundles')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('ewaybill_no')->nullable();
            $table->string('gst_paid_by',50)->nullable();
            $table->string('lr_type', 20)->nullable();
            $table->string('billing_type',50)->nullable();
            $table->string('load_type', 50)->nullable();
            $table->unsignedBigInteger('lr_charges')->nullable();
            $table->string('document_status', 20)->nullable();
            $table->integer('approval_level')
                  ->default(1)
                  ->comment('current approval level');
            

            $table->decimal('sub_total', 15, 2)->nullable();
            $table->decimal('total_charges', 15, 2)->nullable();
            $table->text('remarks', 50)->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_logistics_lorry_receipt_history');
    }
};
