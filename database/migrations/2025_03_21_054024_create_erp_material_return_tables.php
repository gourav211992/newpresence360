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
        Schema::create('erp_material_return_header', function (Blueprint $table) {
            $table->id();
        
            // Organization Structure
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();
        
            // Book & Document Info
            $table->unsignedBigInteger('book_id')->nullable()->index();
            $table->string('book_code', 30)->nullable()->index();
            $table->string('return_type', 50)->nullable()->index();
            $table->string('document_number', 50)->nullable();
            $table->string('doc_number_type', 50)
                  ->default('Manually');
            $table->string('doc_reset_pattern', 50)->nullable();
            $table->string('doc_prefix', 10)->nullable();
            $table->string('doc_suffix', 10)->nullable();
            $table->integer('doc_no')->nullable();
            $table->date('document_date')->nullable()->index();
            $table->string('revision_number', 10)->default('0');
            $table->date('revision_date')->nullable();
            $table->string('reference_number', 50)->nullable();
        
            // Department & Store
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('user_name')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('department_code')->nullable();
            $table->unsignedBigInteger('store_id')->index();
            $table->string('store_code', 50);
        
            // Vendor & Transport
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->string('vendor_code', 50)->nullable();
            $table->string('consignee_name', 100)->nullable();
            $table->string('consignment_no', 50)->nullable();
            $table->string('eway_bill_no', 50)->nullable();
            $table->string('transporter_name', 100)->nullable();
            $table->string('vehicle_no', 50)->nullable();
        
            // Address & Currency
            $table->unsignedBigInteger('billing_address')->nullable();
            $table->unsignedBigInteger('shipping_address')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('currency_code', 30)->nullable();
        
            // Status & Notes
            $table->string('document_status', 50)->nullable()->index();
            $table->integer('approval_level')->default(1)->comment('Current approval level');
            $table->text('remarks')->nullable();
        
            // Financials
            $table->decimal('total_item_value', 15, 2)->default(0.00);
            $table->decimal('total_discount_value', 15, 2)->default(0.00);
            $table->decimal('total_tax_value', 15, 2)->default(0.00);
            $table->decimal('total_expense_value', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
        
            // Multi-Currency
            $table->unsignedBigInteger('org_currency_id')->nullable();
            $table->string('org_currency_code', 10)->nullable();
            $table->decimal('org_currency_exg_rate', 15, 6)->nullable();
        
            $table->unsignedBigInteger('comp_currency_id')->nullable();
            $table->string('comp_currency_code', 10)->nullable();
            $table->decimal('comp_currency_exg_rate', 15, 6)->nullable();
        
            $table->unsignedBigInteger('group_currency_id')->nullable();
            $table->string('group_currency_code', 10)->nullable();
            $table->decimal('group_currency_exg_rate', 15, 6)->nullable();
        
            // Audit Trail
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        
            $table->timestamps();
            $table->softDeletes();
        
        });
        
        Schema::create('erp_mr_items', function (Blueprint $table) {
            $table->id();
        
            $table->unsignedBigInteger('material_return_id')->index()->nullable();
            $table->unsignedBigInteger('mi_item_id')->index()->nullable()->comment('erp_mi_item_id');
            $table->unsignedBigInteger('item_id')->index()->nullable();
            $table->string('item_code', 50)->index()->nullable();
            $table->string('item_name', 255)->nullable();
        
            $table->unsignedBigInteger('hsn_id')->index()->nullable();
            $table->string('hsn_code', 50)->nullable();
        
            $table->unsignedBigInteger('uom_id')->index()->nullable();
            $table->string('uom_code', 20)->nullable();
        
            $table->unsignedBigInteger('store_id')->index()->nullable();            
            $table->string('store_code', 20)->nullable();
            
            $table->unsignedBigInteger('to_store_id')->index()->nullable();
            $table->string('to_store_code', 20)->nullable();
            
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->string('user_name', 20)->nullable();
            $table->unsignedBigInteger('department_id')->index()->nullable();
            $table->string('department_code', 20)->nullable();

            $table->decimal('qty', 20, 6)->default(0.000000);
        
            $table->unsignedBigInteger('inventory_uom_id')->index()->nullable();
            $table->string('inventory_uom_code', 20)->nullable();
            $table->decimal('inventory_uom_qty', 20, 6)->default(0.000000);
        
            $table->decimal('rate', 20, 6)->default(0.000000);
            $table->decimal('item_discount_amount', 15, 2)->default(0.00);
            $table->decimal('header_discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('item_expense_amount', 15, 2)->default(0.00);
            $table->decimal('header_expense_amount', 15, 2)->default(0.00);
            $table->decimal('total_item_amount', 15, 2)->default(0.00);
        
            $table->text('remarks')->nullable();
        
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('erp_mr_item_attributes', function (Blueprint $table) {
            $table->id();
        
            $table->unsignedBigInteger('material_return_id')->index()->nullable();
            $table->unsignedBigInteger('mr_item_id')->index()->nullable();
            $table->unsignedBigInteger('item_attribute_id')->index()->nullable()->comment('use erp_item_attributes');
        
            $table->string('item_code', 100)->nullable();
            $table->string('attribute_name', 100)->nullable();
            $table->unsignedBigInteger('attr_name')->index()->nullable();
        
            $table->string('attribute_value', 100)->nullable();
            $table->unsignedBigInteger('attr_value')->index()->nullable();
        
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('erp_mr_item_locations', function (Blueprint $table) {
            $table->id();
        
            $table->unsignedBigInteger('material_return_id')->index()->nullable();
            $table->unsignedBigInteger('mr_item_id')->index()->nullable();
            $table->unsignedBigInteger('item_id')->index()->nullable();
            $table->string('item_code', 100)->index()->nullable();
        
            $table->unsignedBigInteger('store_id')->index()->nullable();
            $table->string('store_code', 50)->nullable();
        
            $table->unsignedBigInteger('rack_id')->index()->nullable();
            $table->string('rack_code', 50)->nullable();
        
            $table->unsignedBigInteger('shelf_id')->index()->nullable();
            $table->string('shelf_code', 50)->nullable();
        
            $table->unsignedBigInteger('bin_id')->index()->nullable();
            $table->string('bin_code', 50)->nullable();
            
            $table->string('type', 50)->nullable();
            $table->decimal('quantity', 15, 2)->default(0.00);
            $table->decimal('inventory_uom_qty', 15, 2)->default(0.00);
        
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_material_return_media', function (Blueprint $table) {
            $table->id();
        
            $table->morphs('model'); // Automatically indexed
        
            $table->uuid('uuid')->nullable()->unique();
            
            $table->string('model_name', 150)->index();          // Index for filtering
            $table->string('collection_name', 100)->index();
            $table->string('name', 255);                   
            $table->string('file_name', 255);              
            $table->string('mime_type', 100)->nullable();  
            $table->string('disk', 50);                    
            $table->string('conversions_disk', 50)->nullable();
        
            $table->unsignedBigInteger('size')->default(0); 
        
            $table->json('manipulations')->nullable();
            $table->json('custom_properties')->nullable();
            $table->json('generated_conversions')->nullable();
            $table->json('responsive_images')->nullable();
        
            $table->unsignedInteger('order_column')->nullable()->index();
        
            $table->timestamps();
        });

        //History
        Schema::create('erp_material_return_header_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable()->index(); // Index for linkage
        
            // Organization Structure
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();
        
            // Book & Document Info
            $table->unsignedBigInteger('book_id')->nullable()->index();
            $table->string('book_code', 30)->nullable()->index();
            $table->string('return_type', 50)->nullable()->index();
            $table->string('document_number', 50)->nullable();
            $table->string('doc_number_type', 50)
                  ->default('Manually');
            $table->string('doc_reset_pattern', 50)->nullable();
            $table->string('doc_prefix', 10)->nullable();
            $table->string('doc_suffix', 10)->nullable();
            $table->integer('doc_no')->nullable();
            $table->date('document_date')->nullable()->index();
            $table->string('revision_number', 10)->default('0');
            $table->date('revision_date')->nullable();
            $table->string('reference_number', 50)->nullable();
        
            // Department & Store
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('user_name')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('department_code')->nullable();
            $table->unsignedBigInteger('store_id')->index();
            $table->string('store_code', 50);
        
            // Vendor & Transport
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->string('vendor_code', 50)->nullable();
            $table->string('consignee_name', 100)->nullable();
            $table->string('consignment_no', 50)->nullable();
            $table->string('eway_bill_no', 50)->nullable();
            $table->string('transporter_name', 100)->nullable();
            $table->string('vehicle_no', 50)->nullable();
        
            // Address & Currency
            $table->unsignedBigInteger('billing_address')->nullable();
            $table->unsignedBigInteger('shipping_address')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('currency_code', 30)->nullable();
        
            // Status & Notes
            $table->string('document_status', 50)->nullable()->index();
            $table->integer('approval_level')->default(1)->comment('Current approval level');
            $table->text('remarks')->nullable();
        
            // Financials
            $table->decimal('total_item_value', 15, 2)->default(0.00);
            $table->decimal('total_discount_value', 15, 2)->default(0.00);
            $table->decimal('total_tax_value', 15, 2)->default(0.00);
            $table->decimal('total_expense_value', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
        
            // Multi-Currency
            $table->unsignedBigInteger('org_currency_id')->nullable();
            $table->string('org_currency_code', 10)->nullable();
            $table->decimal('org_currency_exg_rate', 15, 6)->nullable();
        
            $table->unsignedBigInteger('comp_currency_id')->nullable();
            $table->string('comp_currency_code', 10)->nullable();
            $table->decimal('comp_currency_exg_rate', 15, 6)->nullable();
        
            $table->unsignedBigInteger('group_currency_id')->nullable();
            $table->string('group_currency_code', 10)->nullable();
            $table->decimal('group_currency_exg_rate', 15, 6)->nullable();
        
            // Audit Trail
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_mr_items_history', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->unsignedBigInteger('material_return_id')->index()->nullable();
            $table->unsignedBigInteger('mi_item_id')->index()->nullable()->comment('erp_mi_item_id');
            $table->unsignedBigInteger('item_id')->index()->nullable();
            $table->string('item_code', 50)->index()->nullable();
            $table->string('item_name', 255)->nullable();
        
            $table->unsignedBigInteger('hsn_id')->index()->nullable();
            $table->string('hsn_code', 50)->nullable();
        
            $table->unsignedBigInteger('uom_id')->index()->nullable();
            $table->string('uom_code', 20)->nullable();
        
            $table->unsignedBigInteger('store_id')->index()->nullable();            
            $table->string('store_code', 20)->nullable();
            
            $table->unsignedBigInteger('to_store_id')->index()->nullable();
            $table->string('to_store_code', 20)->nullable();
            
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->string('user_name', 20)->nullable();
            $table->unsignedBigInteger('department_id')->index()->nullable();
            $table->string('department_code', 20)->nullable();

            $table->decimal('qty', 20, 6)->default(0.000000);
        
            $table->unsignedBigInteger('inventory_uom_id')->index()->nullable();
            $table->string('inventory_uom_code', 20)->nullable();
            $table->decimal('inventory_uom_qty', 20, 6)->default(0.000000);
        
            $table->decimal('rate', 20, 6)->default(0.000000);
            $table->decimal('item_discount_amount', 15, 2)->default(0.00);
            $table->decimal('header_discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('item_expense_amount', 15, 2)->default(0.00);
            $table->decimal('header_expense_amount', 15, 2)->default(0.00);
            $table->decimal('total_item_amount', 15, 2)->default(0.00);
        
            $table->text('remarks')->nullable();
        
            $table->timestamps();
            $table->softDeletes();
        });
        

        Schema::create('erp_mr_item_attributes_history', function (Blueprint $table) {
            $table->id();
        
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->unsignedBigInteger('material_return_id')->nullable()->index();
            $table->unsignedBigInteger('mr_item_id')->nullable()->index();
        
            $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('use erp_item_attributes');
            $table->string('item_code', 50)->nullable();
            
            $table->string('attribute_name', 100)->nullable();
            $table->unsignedBigInteger('attr_name')->nullable();
            
            $table->string('attribute_value', 100)->nullable();
            $table->unsignedBigInteger('attr_value')->nullable();
        
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_mr_item_locations_history', function (Blueprint $table) {
            $table->id();
        
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->unsignedBigInteger('material_return_id')->index()->nullable();
            $table->unsignedBigInteger('mr_item_id')->index()->nullable();
            $table->unsignedBigInteger('item_id')->index()->nullable();
            $table->string('item_code', 100)->index()->nullable();
        
            $table->unsignedBigInteger('store_id')->index()->nullable();
            $table->string('store_code', 50)->nullable();
        
            $table->unsignedBigInteger('rack_id')->index()->nullable();
            $table->string('rack_code', 50)->nullable();
        
            $table->unsignedBigInteger('shelf_id')->index()->nullable();
            $table->string('shelf_code', 50)->nullable();
        
            $table->unsignedBigInteger('bin_id')->index()->nullable();
            $table->string('bin_code', 50)->nullable();
            
            $table->string('type', 50)->nullable();
            $table->decimal('quantity', 15, 2)->default(0.00);
            $table->decimal('inventory_uom_qty', 15, 2)->default(0.00);
        
            $table->timestamps();
            $table->softDeletes();
        });

        //Material Issue Update
        Schema::table('erp_mi_items', function (Blueprint $table) {
            $table->double('mr_qty',20,6)->default(0)->after('issue_qty'); // adjust 'after' as needed
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_mr_item_attributes');
        Schema::dropIfExists('erp_mr_item_attributes_history');
        Schema::dropIfExists('erp_mr_item_locations');
        Schema::dropIfExists('erp_mr_item_locations_history');
        Schema::dropIfExists('erp_mr_items');
        Schema::dropIfExists('erp_mr_items_history');
        Schema::dropIfExists('erp_material_return_header');
        Schema::dropIfExists('erp_material_return_header_history');
        Schema::dropIfExists('erp_material_return_media');
        if (Schema::hasColumn('erp_mi_items', 'mr_qty')) {
            Schema::table('erp_mi_items', function (Blueprint $table) {
                $table->dropColumn('mr_qty');
            });
        }
    }
};
