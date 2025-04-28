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
        Schema::create('erp_purchase_indents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id')->nullable(); 
            $table->string('book_code')->nullable(); 
            $table->string('document_number')->nullable(); 
            $table->date('document_date')->nullable(); 
            $table->string('revision_number')->default(0); 
            $table->date('revision_date')->nullable(); 
            $table->string('reference_number')->nullable();
            $table->integer('approval_level')->default(1)->comment('current approval level'); 
            $table->text('remarks')->nullable();
            $table->string('document_status')->default()->index();

            $table->unsignedBigInteger('org_currency_id')->nullable();
            $table->string('org_currency_code')->nullable();
            $table->decimal('org_currency_exg_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('comp_currency_id')->nullable();
            $table->string('comp_currency_code')->nullable();
            $table->decimal('comp_currency_exg_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('group_currency_id')->nullable();
            $table->string('group_currency_code')->nullable();
            $table->decimal('group_currency_exg_rate', 15, 6)->nullable();

            $table->string('user_type')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            // $table->foreign('created_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->unsignedBigInteger('updated_by')->nullable();
            // $table->foreign('updated_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->unsignedBigInteger('deleted_by')->nullable();
            // $table->foreign('deleted_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_pi_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pi_id')->nullable()->comment('erp_purchase_indents id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->unsignedBigInteger('hsn_id')->nullable();
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();

            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('vendor_code')->nullable();
            $table->string('vendor_name')->nullable();

            $table->decimal('order_qty',15, 2)->default(0.00);
            // $table->decimal('grn_qty',15, 2)->default(0.00);
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->decimal('inventory_uom_qty',15, 2)->default(0.00);
            $table->text('remarks')->nullable();
            $table->foreign('pi_id')->references('id')->on('erp_purchase_indents')->onDelete('cascade');
            $table->timestamps(); 
            $table->softDeletes();
        });

        Schema::create('erp_pi_item_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pi_id')->nullable();
            $table->unsignedBigInteger('pi_item_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('use tbl erp_item_attributes');
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('attribute_group_id')->nullable();
            $table->unsignedBigInteger('attribute_id')->nullable();
            $table->string('attribute_name')->nullable();
            $table->string('attribute_value')->nullable();

            $table->foreign('pi_item_id')->references('id')->on('erp_pi_items')->onDelete('cascade');
            $table->foreign('pi_id')->references('id')->on('erp_purchase_indents')->onDelete('cascade');

            $table->timestamps(); 
            $table->softDeletes();
        });

        Schema::create('erp_pi_item_delivery', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pi_id')->nullable();
            $table->unsignedBigInteger('pi_item_id')->nullable();
            $table->decimal('qty',15, 2)->default(0.00);
            $table->decimal('grn_qty',15, 2)->default(0.00);
            $table->date('delivery_date');
            $table->foreign('pi_item_id')->references('id')->on('erp_pi_items')->onDelete('cascade');
            $table->foreign('pi_id')->references('id')->on('erp_purchase_indents')->onDelete('cascade');

            $table->timestamps(); 
            $table->softDeletes();
        });

        Schema::create('erp_purchase_indents_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id')->nullable(); 
            $table->string('book_code')->nullable(); 
            $table->string('document_number')->nullable(); 
            $table->date('document_date')->nullable(); 
            $table->string('revision_number')->default(0); 
            $table->date('revision_date')->nullable(); 
            $table->string('reference_number')->nullable();
            $table->integer('approval_level')->default(1)->comment('current approval level'); 
            $table->text('remarks')->nullable();
            $table->string('document_status')->default()->index();

            $table->unsignedBigInteger('org_currency_id')->nullable();
            $table->string('org_currency_code')->nullable();
            $table->decimal('org_currency_exg_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('comp_currency_id')->nullable();
            $table->string('comp_currency_code')->nullable();
            $table->decimal('comp_currency_exg_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('group_currency_id')->nullable();
            $table->string('group_currency_code')->nullable();
            $table->decimal('group_currency_exg_rate', 15, 6)->nullable();

            $table->string('user_type')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            // $table->foreign('created_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->unsignedBigInteger('updated_by')->nullable();
            // $table->foreign('updated_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->unsignedBigInteger('deleted_by')->nullable();
            // $table->foreign('deleted_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_pi_items_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('pi_id')->nullable()->comment('erp_purchase_indents id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->unsignedBigInteger('hsn_id')->nullable();
            $table->string('hsn_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();

            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('vendor_code')->nullable();
            $table->string('vendor_name')->nullable();

            $table->decimal('order_qty',15, 2)->default(0.00);
            // $table->decimal('grn_qty',15, 2)->default(0.00);
            $table->unsignedBigInteger('inventory_uom_id')->nullable();
            $table->string('inventory_uom_code')->nullable();
            $table->decimal('inventory_uom_qty',15, 2)->default(0.00);
            $table->text('remarks')->nullable();
            $table->foreign('pi_id')->references('id')->on('erp_purchase_indents_history')->onDelete('cascade');
            $table->timestamps(); 
            $table->softDeletes();
        });

        Schema::create('erp_pi_item_attributes_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('pi_id')->nullable();
            $table->unsignedBigInteger('pi_item_id')->nullable();
            $table->unsignedBigInteger('item_attribute_id')->nullable()->comment('use tbl erp_item_attributes');
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('attribute_group_id')->nullable();
            $table->unsignedBigInteger('attribute_id')->nullable();
            $table->string('attribute_name')->nullable();
            $table->string('attribute_value')->nullable();

            $table->foreign('pi_item_id')->references('id')->on('erp_pi_items_history')->onDelete('cascade');
            $table->foreign('pi_id')->references('id')->on('erp_purchase_indents_history')->onDelete('cascade');

            $table->timestamps(); 
            $table->softDeletes();
        });

        Schema::create('erp_pi_item_delivery_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('pi_id')->nullable();
            $table->unsignedBigInteger('pi_item_id')->nullable();
            $table->decimal('qty',15, 2)->default(0.00);
            $table->decimal('grn_qty',15, 2)->default(0.00);
            $table->date('delivery_date');
            $table->foreign('pi_item_id')->references('id')->on('erp_pi_items_history')->onDelete('cascade');
            $table->foreign('pi_id')->references('id')->on('erp_purchase_indents_history')->onDelete('cascade');

            $table->timestamps(); 
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_pi_item_delivery');
        Schema::dropIfExists('erp_pi_item_attributes');
        Schema::dropIfExists('erp_pi_items');
        Schema::dropIfExists('erp_purchase_indents');

        Schema::dropIfExists('erp_pi_item_delivery_history');
        Schema::dropIfExists('erp_pi_item_attributes_history');
        Schema::dropIfExists('erp_pi_items_history');
        Schema::dropIfExists('erp_purchase_indents_history');
    }
};
