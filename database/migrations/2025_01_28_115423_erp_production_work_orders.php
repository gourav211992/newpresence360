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
        //
        Schema::create('erp_production_work_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('book_id')->nullable();
            $table->string('book_code')->nullable();
            $table->string('document_number')->nullable();
            $table->enum('station_wise_consumption', ['yes', 'no'])->default('no');
            $table->enum('doc_number_type', ['Auto', 'Manually'])->default('Manually');
            $table->enum('doc_reset_pattern', ['Never', 'Yearly', 'Quarterly', 'Monthly'])->nullable();
            $table->string('doc_prefix')->nullable();
            $table->string('doc_suffix')->nullable();
            $table->integer('doc_no')->nullable();
            $table->date('document_date')->nullable();
            $table->string('revision_number')->default('0');
            $table->date('revision_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->integer('approval_level')->default(1)->comment('current approval level');
            $table->text('remarks')->nullable();
            $table->string('document_status')->nullable();
            //$table->string('user_type')->nullable();//to be disscussed
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // $table->json('attachment')->nullable();

            $table->foreign('location_id')->references('id')->on('erp_stores');
            $table->foreign('book_id')->references('id')->on('erp_books');
        });

        Schema::create('erp_production_work_orders_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('book_id')->nullable();
            $table->string('book_code')->nullable();
            $table->string('document_number')->nullable();
            $table->enum('station_wise_consumption', ['yes', 'no'])->default('no');
            $table->enum('doc_number_type', ['Auto', 'Manually'])->default('Manually');
            $table->enum('doc_reset_pattern', ['Never', 'Yearly', 'Quarterly', 'Monthly'])->nullable();
            $table->string('doc_prefix')->nullable();
            $table->string('doc_suffix')->nullable();
            $table->integer('doc_no')->nullable();
            $table->date('document_date')->nullable();
            $table->string('revision_number')->default('0');
            $table->date('revision_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->integer('approval_level')->default(1)->comment('current approval level');
            $table->text('remarks')->nullable();
            $table->string('document_status')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // $table->json('attachment')->nullable();

            $table->foreign('source_id')->references('id')->on('erp_production_work_orders');
            $table->foreign('location_id')->references('id')->on('erp_stores');
            $table->foreign('book_id')->references('id')->on('erp_books');

        });

        Schema::create('erp_pwo_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pwo_id')->nullable()->comment('erp_production_work_orders id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->unsignedBigInteger('hsn_id')->nullable();
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();
            $table->decimal('order_qty', 15, 2)->default(0.00);
            $table->decimal('manf_order_qty', 15, 2)->default(0.00);
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->decimal('inventory_uom_qty', 15, 2)->default(0.00);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->foreign('pwo_id')->references('id')->on('erp_production_work_orders');
            $table->foreign('item_id')->references('id')->on('erp_items');
            $table->foreign('hsn_id')->references('id')->on('erp_hsns');
            $table->foreign('uom_id')->references('id')->on('erp_units');
            $table->foreign('inventory_uom_id')->references('id')->on('erp_units');
        });

        Schema::create('erp_pwo_items_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('pwo_id')->nullable()->comment('erp_production_work_orders id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->unsignedBigInteger('hsn_id')->nullable();
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();
            $table->decimal('order_qty', 15, 2)->default(0.00);
            $table->decimal('manf_order_qty', 15, 2)->default(0.00);
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->decimal('inventory_uom_qty', 15, 2)->default(0.00);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->foreign('source_id')->references('id')->on('erp_pwo_items');
            $table->foreign('pwo_id')->references('id')->on('erp_production_work_orders_history');
            $table->foreign('item_id')->references('id')->on('erp_items');
            $table->foreign('hsn_id')->references('id')->on('erp_hsns');
            $table->foreign('uom_id')->references('id')->on('erp_units');
            $table->foreign('inventory_uom_id')->references('id')->on('erp_units');

        });

        Schema::create('erp_pwo_item_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pwo_id')->nullable();
            $table->unsignedBigInteger('pwo_item_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('use tbl erp_item_attributes');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('attribute_group_id')->nullable();
            $table->unsignedBigInteger('attribute_id')->nullable();
            $table->string('attribute_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('pwo_id')->references('id')->on('erp_production_work_orders');
            $table->foreign('pwo_item_id')->references('id')->on('erp_pwo_items');
            $table->foreign('item_id')->references('id')->on('erp_items');
            $table->foreign('attribute_id')->references('id')->on('erp_attributes');
            $table->foreign('item_attribute_id')->references('id')->on('erp_item_attributes');

        });

        Schema::create('erp_pwo_item_attributes_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('pwo_id')->nullable();
            $table->unsignedBigInteger('pwo_item_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('use tbl erp_item_attributes');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('attribute_group_id')->nullable();
            $table->unsignedBigInteger('attribute_id')->nullable();
            $table->string('attribute_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('source_id')->references('id')->on('erp_pwo_item_attributes');
            $table->foreign('pwo_id')->references('id')->on('erp_production_work_orders_history');
            $table->foreign('item_id')->references('id')->on('erp_items');
            $table->foreign('pwo_item_id')->references('id')->on('erp_pwo_items_history');
            $table->foreign('item_attribute_id')->references('id')->on('erp_item_attributes');
            $table->foreign('attribute_id')->references('id')->on('erp_attributes');

        });

        Schema::create('erp_pwo_media', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->char('uuid', 36)->nullable();
            $table->string('model_name');
            $table->string('collection_name');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('disk');
            $table->string('conversions_disk')->nullable();
            $table->unsignedBigInteger('size');
            $table->json('manipulations');
            $table->json('custom_properties');
            $table->json('generated_conversions');
            $table->json('responsive_images');
            $table->unsignedInteger('order_column')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        Schema::create('erp_pwo_so_mapping', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pwo_id')->nullable();
            // $table->unsignedBigInteger('pwo_item_id')->nullable();
            $table->unsignedBigInteger('so_id');
            $table->unsignedBigInteger('so_item_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_code');
            $table->json('attributes')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();
            $table->decimal('qty', 20, 6)->default(0);

            $table->double('mo_product_qty',20,6)->default(0);
            $table->double('mo_value', 20, 6)->default(0);
            $table->double('pslip_qty', 20, 6)->default(0);
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->double('inventory_uom_qty',20,6)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('so_id')->references('id')->on('erp_sale_orders')->onDelete('cascade');
            $table->foreign('so_item_id')->references('id')->on('erp_so_items')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('erp_items')->onDelete('cascade');
            $table->foreign('pwo_id')->references('id')->on('erp_production_work_orders')->onDelete('set null');
            // $table->foreign('pwo_item_id')->references('id')->on('erp_pwo_items')->onDelete('set null');
        });

        Schema::create('erp_pwo_so_mapping_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('pwo_id')->nullable();
            // $table->unsignedBigInteger('pwo_item_id')->nullable();
            $table->unsignedBigInteger('so_id');
            $table->unsignedBigInteger('so_item_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_code');
            $table->json('attributes')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();
            $table->decimal('qty', 20, 6)->default(0);

            $table->double('mo_product_qty',20,6)->default(0);
            $table->double('mo_value', 20, 6)->default(0);
            $table->double('pslip_qty', 20, 6)->default(0);
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->double('inventory_uom_qty',20,6)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('so_id')->references('id')->on('erp_sale_orders')->onDelete('cascade');
            $table->foreign('so_item_id')->references('id')->on('erp_so_items')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('erp_items')->onDelete('cascade');
            $table->foreign('pwo_id')->references('id')->on('erp_production_work_orders')->onDelete('set null');
            // $table->foreign('pwo_item_id')->references('id')->on('erp_pwo_items')->onDelete('set null');
        });

        Schema::create('erp_pwo_station_consumptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pwo_mapping_id')->nullable();
            $table->unsignedBigInteger('station_id')->nullable();
            $table->double('mo_product_qty', 20,6)->default(0);
            $table->double('mo_value', 20,6)->default(0);
            $table->timestamps();
            
            $table->foreign('pwo_mapping_id')->references('id')->on('erp_pwo_so_mapping');
        });
        Schema::create('erp_pwo_station_consumptions_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('pwo_mapping_id')->nullable();
            $table->unsignedBigInteger('station_id')->nullable();
            $table->double('mo_product_qty', 20,6)->default(0);
            $table->double('mo_value', 20,6)->default(0);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_pwo_station_consumptions_history');
        Schema::dropIfExists('erp_pwo_station_consumptions');
        Schema::dropIfExists('erp_pwo_so_mapping_history');
        Schema::dropIfExists('erp_pwo_so_mapping');
        Schema::dropIfExists('erp_pwo_media');
        Schema::dropIfExists('erp_pwo_item_attributes_history');
        Schema::dropIfExists('erp_pwo_items_history');
        Schema::dropIfExists('erp_production_work_orders_history');
        Schema::dropIfExists('erp_pwo_item_attributes');
        Schema::dropIfExists('erp_pwo_items');
        Schema::dropIfExists('erp_production_work_orders');
    }
};
