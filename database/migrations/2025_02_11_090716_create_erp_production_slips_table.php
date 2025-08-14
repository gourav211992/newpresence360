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
        # Production Slip
        foreach(['erp_production_slips', 'erp_production_slips_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->unsignedBigInteger('group_id')->nullable();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('store_id')->nullable();
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
                $table->integer('approval_level')->default(1)->comment('Current Approval Level');
                $table->text('remarks')->nullable();

                $table->decimal('total_item_value', 15, 2)->default(0.00);
                $table->decimal('total_discount_value', 15, 2)->default(0.00);
                $table->decimal('total_tax_value', 15, 2)->default(0.00);
                $table->decimal('total_expense_value', 15, 2)->default(0.00);
                $table->decimal('total_amount', 15, 2)->default(0.00);

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
            });
        }

        # Production Slip Items
        foreach(['erp_pslip_items','erp_pslip_items_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('pslip_id')->nullable();
                $table->unsignedBigInteger('production_bom_id')->nullable()->comment('erp_boms id');
                $table->unsignedBigInteger('item_id')->nullable();
                $table->unsignedBigInteger('so_item_id')->nullable();
                $table->string('item_code')->nullable();
                $table->string('item_name')->nullable();
                $table->unsignedBigInteger('hsn_id')->nullable();
                $table->string('hsn_code')->nullable();
                $table->unsignedBigInteger('uom_id')->nullable();
                $table->string('uom_code')->nullable();
                $table->unsignedBigInteger('store_id');
                $table->decimal('qty',15, 2)->default(0.00);
                $table->unsignedBigInteger('inventory_uom_id')->nullable();
                $table->string('inventory_uom_code')->nullable();
                $table->decimal('inventory_uom_qty',15, 2)->default(0.00);
                $table->decimal('rate', 15, 2)->default(0.00);
                $table->decimal('item_discount_amount', 15, 2)->default(0.00);
                $table->decimal('header_discount_amount', 15, 2)->default(0.00);
                $table->decimal('tax_amount', 15, 2)->default(0.00);
                $table->decimal('item_expense_amount', 15, 2)->default(0.00);
                $table->decimal('header_expense_amount', 15, 2)->default(0.00);
                $table->decimal('total_item_amount', 15, 2)->default(0.00);
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->text('remarks')->nullable();
                if (str_contains($tbl, 'history')) {
                    $table->foreign('pslip_id')->references('id')->on('erp_production_slips_history')->onDelete('cascade');
                } else {
                    $table->foreign('pslip_id')->references('id')->on('erp_production_slips')->onDelete('cascade');
                }
                $table->foreign('customer_id')->references('id')->on('erp_customers')->onDelete('cascade');
                $table->timestamps();
            });
        }

         # Production Slip Item Attributes
         foreach(['erp_pslip_item_attributes','erp_pslip_item_attributes_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('pslip_id')->nullable();
                $table->unsignedBigInteger('pslip_item_id')->nullable();
                $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('use tbl erp_item_attributes');
                $table->string('item_code')->nullable();
                $table->string('attribute_name')->nullable();
                $table->unsignedBigInteger('attr_name')->nullable();
                $table->string('attribute_value')->nullable();
                $table->unsignedBigInteger('attr_value')->nullable();
                if (str_contains($tbl, 'history')) {
                    $table->foreign('pslip_id')->references('id')->on('erp_production_slips_history')->onDelete('cascade');
                    $table->foreign('pslip_item_id')->references('id')->on('erp_pslip_items_history')->onDelete('cascade');
                } else {
                    $table->foreign('pslip_id')->references('id')->on('erp_production_slips')->onDelete('cascade');
                    $table->foreign('pslip_item_id')->references('id')->on('erp_pslip_items')->onDelete('cascade');
                }
                $table->timestamps();
            });
        }

        #Production Slip Item Locations table
        foreach(['erp_pslip_item_locations', 'erp_pslip_item_locations_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('pslip_id')->nullable();
                $table->unsignedBigInteger('pslip_item_id')->nullable();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('item_code')->nullable();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->string('store_code')->nullable();
                $table->unsignedBigInteger('rack_id')->nullable();
                $table->string('rack_code')->nullable();
                $table->unsignedBigInteger('shelf_id')->nullable();
                $table->string('shelf_code')->nullable();
                $table->unsignedBigInteger('bin_id')->nullable();
                $table->string('bin_code')->nullable();
                $table->double('quantity', 15, 2)->default(0.00);
                $table->double('inventory_uom_qty', 15, 2)->default(0.00);
                if (str_contains($tbl, 'history')) {
                    $table->foreign('pslip_item_id')->references('id')->on('erp_pslip_items_history')->onDelete('cascade');
                    $table->foreign('pslip_id')->references('id')->on('erp_production_slips_history')->onDelete('cascade');
                } else {
                    $table->foreign('pslip_item_id')->references('id')->on('erp_pslip_items')->onDelete('cascade');
                    $table->foreign('pslip_id')->references('id')->on('erp_production_slips')->onDelete('cascade');
                }
                $table->foreign('store_id')->references('id')->on('erp_stores')->onDelete('cascade');
                $table->foreign('rack_id')->references('id')->on('erp_racks')->onDelete('cascade');
                $table->foreign('shelf_id')->references('id')->on('erp_shelfs')->onDelete('cascade');
                $table->foreign('bin_id')->references('id')->on('erp_bins')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
            });
        }

        # Production Slip Item Details
        foreach(['erp_pslip_item_details', 'erp_pslip_item_details_history'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) use ($tbl) {
                $table->id();
                if (str_contains($tbl, 'history')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                $table->unsignedBigInteger('pslip_id')->nullable();
                $table->unsignedBigInteger('pslip_item_id')->nullable();
                $table->string('bundle_no');
                $table->string('bundle_type');
                $table->double('qty',20,6)->default(0);
                $table->timestamps();
                if (str_contains($tbl, 'history')) {
                    $table->foreign('pslip_id')->references('id')->on('erp_production_slips_history')->onDelete('cascade');
                    $table->foreign('pslip_item_id')->references('id')->on('erp_pslip_items_history')->onDelete('cascade');
                } else {
                    $table->foreign('pslip_id')->references('id')->on('erp_production_slips')->onDelete('cascade');
                    $table->foreign('pslip_item_id')->references('id')->on('erp_pslip_items')->onDelete('cascade');
                }
            });
        }
        
        # Manufactoring Order Media
        Schema::create('erp_pslip_media', function (Blueprint $table) use ($tbl) {
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
        Schema::dropIfExists('erp_pslip_media');
        Schema::dropIfExists('erp_pslip_item_details_history');
        Schema::dropIfExists('erp_pslip_item_details');
        Schema::dropIfExists('erp_pslip_item_attributes_history');
        Schema::dropIfExists('erp_pslip_item_attributes');
        Schema::dropIfExists('erp_pslip_item_locations_history');
        Schema::dropIfExists('erp_pslip_item_locations');
        Schema::dropIfExists('erp_pslip_items_history');
        Schema::dropIfExists('erp_pslip_items');
        Schema::dropIfExists('erp_production_slips_history');
        Schema::dropIfExists('erp_production_slips');
    }
};
