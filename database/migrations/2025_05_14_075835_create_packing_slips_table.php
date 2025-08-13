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
        Schema::create('erp_packing_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('book_id');
            $table->string('book_code');
            $table->string('document_number');
            $table->string('doc_number_type', 20) -> default('Manually');
            $table->string('doc_reset_pattern', 25) -> nullable();
            $table->string('doc_prefix', 100) -> nullable();
            $table->string('doc_suffix', 100) -> nullable();
            $table->integer('doc_no') -> nullable();
            $table->date('document_date');
            $table->string('revision_number')->default(0);
            $table->date('revision_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('document_status')->nullable();
            $table->integer('approval_level')->default(1)->comment('current approval level');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            //$table->foreign('created_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->unsignedBigInteger('updated_by')->nullable();
            //$table->foreign('updated_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->unsignedBigInteger('deleted_by')->nullable();
            //$table->foreign('deleted_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_packing_list_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plist_id');
            $table->unsignedBigInteger('sale_order_id')->nullable();
            $table->unsignedBigInteger('dn_item_id')->nullable();
            $table->string('packing_number', 100);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_packing_list_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plist_id');
            $table->unsignedBigInteger('plist_detail_id');
            $table->unsignedBigInteger('sale_order_id')->nullable();
            $table->unsignedBigInteger('so_item_id')->nullable();
            $table->unsignedBigInteger('item_id');
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->double('qty', 20, 6) -> default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_packing_list_item_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plist_id')->nullable();
            $table->unsignedBigInteger('plist_detail_id')->nullable();
            $table->unsignedBigInteger('plist_item_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('use tbl erp_item_attributes');
            $table->string('item_code')->nullable();
            $table->string('attribute_name')->nullable();
            $table->unsignedBigInteger('attr_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->unsignedBigInteger('attr_value')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_packing_lists_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('book_id');
            $table->string('book_code');
            $table->string('document_number');
            $table->date('document_date');
            $table->string('revision_number')->default(0);
            $table->date('revision_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('document_status')->nullable();
            $table->integer('approval_level')->default(1)->comment('current approval level');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            //$table->foreign('created_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->unsignedBigInteger('updated_by')->nullable();
            //$table->foreign('updated_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->unsignedBigInteger('deleted_by')->nullable();
            //$table->foreign('deleted_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_packing_list_details_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('plist_id');
            $table->unsignedBigInteger('sale_order_id')->nullable();
            $table->unsignedBigInteger('dn_item_id')->nullable();
            $table->string('packing_number', 100);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_packing_list_items_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('plist_id');
            $table->unsignedBigInteger('plist_detail_id');
            $table->unsignedBigInteger('sale_order_id')->nullable();
            $table->unsignedBigInteger('so_item_id')->nullable();
            $table->unsignedBigInteger('item_id');
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->double('qty', 20, 6) -> default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_packing_list_item_attributes_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('plist_id')->nullable();
            $table->unsignedBigInteger('plist_detail_id')->nullable();
            $table->unsignedBigInteger('plist_item_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('use tbl erp_item_attributes');
            $table->string('attribute_name')->nullable();
            $table->unsignedBigInteger('attr_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->unsignedBigInteger('attr_value')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_plist_media', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->uuid()->nullable()->unique();
            $table->string('model_name', 100);
            $table->string('collection_name', 50);
            $table->string('name', 100);
            $table->string('file_name');
            $table->string('mime_type', 50)->nullable();
            $table->string('disk', 100);
            $table->string('conversions_disk', 100)->nullable();
            $table->unsignedBigInteger('size');
            $table->json('manipulations')->nullable();
            $table->json('custom_properties')->nullable();
            $table->json('generated_conversions')->nullable();
            $table->json('responsive_images')->nullable();
            $table->unsignedInteger('order_column')->nullable()->index();
            $table->nullableTimestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_plist_media_table');
        Schema::dropIfExists('erp_packing_list_item_attributes_history');
        Schema::dropIfExists('erp_packing_list_items_history');
        Schema::dropIfExists('erp_packing_list_details_history');
        Schema::dropIfExists('erp_packing_lists_history');
        Schema::dropIfExists('erp_packing_list_item_attributes');
        Schema::dropIfExists('erp_packing_list_items');
        Schema::dropIfExists('erp_packing_list_details');
        Schema::dropIfExists('erp_packing_lists');
    }
};
