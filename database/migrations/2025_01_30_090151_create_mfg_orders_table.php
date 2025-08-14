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
    public function up(): void
    {
        # Manufactoring Order
        foreach(['erp_mfg_orders', 'erp_mfg_orders_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('store_id')->nullable();
                $table->unsignedBigInteger('station_id')->nullable();
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->unsignedBigInteger('group_id')->nullable();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('book_id')->comment('books tbl id')->nullable();
                $table->string('book_code')->nullable();
                $table->string('document_number')->nullable();
                $table->date('document_date')->nullable();
                $table->enum('doc_number_type', ConstantHelper::DOC_NO_TYPES)->default(ConstantHelper::DOC_NO_TYPE_MANUAL);
                $table->enum('doc_reset_pattern', ConstantHelper::DOC_RESET_PATTERNS)->nullable();
                $table->string('doc_prefix')->nullable();
                $table->string('doc_suffix')->nullable();
                $table->integer('doc_no')->nullable();
                $table->string('document_status')->nullable();
                $table->integer('revision_number')->default(0);
                $table->date('revision_date')->nullable();
                $table->text('remarks')->nullable();
                $table->integer('approval_level')->default(1)->comment('Current Approval Level');

                $table->unsignedBigInteger('currency_id')->nullable();
                $table->string('currency_code')->nullable();
                $table->unsignedBigInteger('org_currency_id')->nullable();
                $table->string('org_currency_code')->nullable();
                $table->decimal('org_currency_exg_rate', 15, 6)->nullable();
                $table->unsignedBigInteger('comp_currency_id')->nullable();
                $table->string('comp_currency_code')->nullable();
                $table->decimal('comp_currency_exg_rate', 15, 6)->nullable();
                $table->unsignedBigInteger('group_currency_id')->nullable();
                $table->string('group_currency_code')->nullable();
                $table->decimal('group_currency_exg_rate', 15, 6)->nullable();

                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('store_id')->references('id')->on('erp_stores')->onDelete('cascade');
                $table->foreign('station_id')->references('id')->on('erp_stations')->onDelete('cascade');
            });
        }

        # Manufactoring Product Item
        foreach(['erp_mo_products','erp_mo_products_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('mo_id')->nullable();
                $table->unsignedBigInteger('production_bom_id')->nullable()->comment('erp_boms id');
                $table->unsignedBigInteger('item_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('item_code')->nullable();
                $table->unsignedBigInteger('uom_id')->nullable();
                $table->double('qty',[20,6])->default(0);
                $table->unsignedBigInteger('pwo_mapping_id')->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->text('remark')->nullable();
                if (str_contains($tbl, 'history')) {
                    $table->foreign('mo_id')->references('id')->on('erp_mfg_orders_history')->onDelete('cascade');
                } else {
                    $table->foreign('mo_id')->references('id')->on('erp_mfg_orders')->onDelete('cascade');
                }
                $table->timestamps();
            });
        }

         # Manufactoring Product Item Attribute
         foreach(['erp_mo_product_attributes','erp_mo_product_attributes_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('mo_id')->nullable();
                $table->unsignedBigInteger('mo_product_id')->nullable();
                $table->unsignedBigInteger('item_attribute_id')->nullable();
                $table->string('item_code')->nullable();
                // $table->unsignedBigInteger('attribute_group_id')->nullable();
                $table->unsignedBigInteger('attribute_name')->nullable();
                $table->unsignedBigInteger('attribute_value')->nullable();
                if (str_contains($tbl, 'history')) {
                    $table->foreign('mo_id')->references('id')->on('erp_mfg_orders_history')->onDelete('cascade');
                    $table->foreign('mo_product_id')->references('id')->on('erp_mo_products_history')->onDelete('cascade');
                } else {
                    $table->foreign('mo_id')->references('id')->on('erp_mfg_orders')->onDelete('cascade');
                    $table->foreign('mo_product_id')->references('id')->on('erp_mo_products')->onDelete('cascade');
                }
                $table->timestamps();
            });
        }

        # Manufactoring Item
        foreach(['erp_mo_items', 'erp_mo_items_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('mo_id')->nullable();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('item_code')->nullable();
                $table->unsignedBigInteger('uom_id')->nullable();
                $table->double('qty',[20,6])->default(0);
                $table->double('rate',[20,6])->default(0);
                $table->unsignedBigInteger('inventory_uom_id')->nullable();
                $table->string('inventory_uom_code')->nullable();
                $table->double('inventory_uom_qty',[20,6])->default(0);
                $table->timestamps();
                if (str_contains($tbl, 'history')) {
                    $table->foreign('mo_id')->references('id')->on('erp_mfg_orders_history')->onDelete('cascade');
                } else {
                    $table->foreign('mo_id')->references('id')->on('erp_mfg_orders')->onDelete('cascade');
                }
            });
        }

        # Manufactoring Item
        foreach(['erp_mo_bom_mapping', 'erp_mo_bom_mapping_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('mo_id')->nullable();
                $table->unsignedBigInteger('mo_product_id')->nullable();
                $table->unsignedBigInteger('bom_id')->nullable();
                $table->unsignedBigInteger('bom_detail_id')->nullable();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('item_code')->nullable();
                $table->json('attributes')->nullable();
                $table->unsignedBigInteger('uom_id')->nullable();
                $table->double('qty',[20,6])->default(0);
                $table->timestamps();
                if (str_contains($tbl, 'history')) {
                    $table->foreign('mo_id')->references('id')->on('erp_mfg_orders_history')->onDelete('cascade');
                    $table->foreign('mo_product_id')->references('id')->on('erp_mo_products_history')->onDelete('cascade');
                    $table->foreign('bom_id')->references('id')->on('erp_boms_history')->onDelete('cascade');
                    $table->foreign('bom_detail_id')->references('id')->on('erp_bom_details_history')->onDelete('cascade');
                } else {
                    $table->foreign('mo_id')->references('id')->on('erp_mfg_orders')->onDelete('cascade');
                    $table->foreign('mo_product_id')->references('id')->on('erp_mo_products')->onDelete('cascade');
                    $table->foreign('bom_id')->references('id')->on('erp_boms')->onDelete('cascade');
                    $table->foreign('bom_detail_id')->references('id')->on('erp_bom_details')->onDelete('cascade');
                }
            });
        }

        # Manufactoring Item Attributes
        foreach(['erp_mo_item_attributes','erp_mo_item_attributes_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('mo_id')->nullable();
                $table->unsignedBigInteger('mo_item_id')->nullable();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('use tbl erp_item_attributes');
                $table->string('item_code')->nullable();
                $table->unsignedBigInteger('attribute_name')->nullable();
                $table->unsignedBigInteger('attribute_value')->nullable();
                $table->timestamps();
                if (str_contains($tbl, 'history')) {
                    $table->foreign('mo_item_id')->references('id')->on('erp_mo_items_history')->onDelete('cascade');
                    $table->foreign('mo_id')->references('id')->on('erp_mfg_orders_history')->onDelete('cascade');
                } else {
                    $table->foreign('mo_item_id')->references('id')->on('erp_mo_items')->onDelete('cascade');
                    $table->foreign('mo_id')->references('id')->on('erp_mfg_orders')->onDelete('cascade');
                }
            });
        }

        # Manufactoring Order Media
        Schema::create('erp_mo_media', function (Blueprint $table) use ($tbl) {
            $table->id();
            $table->morphs('model');
            $table->uuid()->nullable()->unique();
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
            $table->unsignedInteger('order_column')->nullable()->index();
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_mo_media');
        Schema::dropIfExists('erp_mo_bom_mapping_history');
        Schema::dropIfExists('erp_mo_bom_mapping');
        Schema::dropIfExists('erp_mo_item_attributes_history');
        Schema::dropIfExists('erp_mo_item_attributes');
        Schema::dropIfExists('erp_mo_items_history');
        Schema::dropIfExists('erp_mo_items');
        Schema::dropIfExists('erp_mo_product_attributes_history');
        Schema::dropIfExists('erp_mo_product_attributes');
        Schema::dropIfExists('erp_mo_products_history');
        Schema::dropIfExists('erp_mo_products');
        Schema::dropIfExists('erp_mfg_orders_history');
        Schema::dropIfExists('erp_mfg_orders');
    }
};
