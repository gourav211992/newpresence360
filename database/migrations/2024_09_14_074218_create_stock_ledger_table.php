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
        Schema::create('stock_ledger', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('group_id')->index(); 
            $table->unsignedBigInteger('company_id')->index(); 
            $table->unsignedBigInteger('organization_id')->index(); 
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('rack_id')->nullable();
            $table->unsignedBigInteger('shelf_id')->nullable();
            $table->unsignedBigInteger('bin_id')->nullable();
            $table->string('store', 150)->nullable();
            $table->string('rack', 50)->nullable();
            $table->string('shelf', 50)->nullable();
            $table->string('bin', 50)->nullable();
            $table->unsignedBigInteger('document_header_id')->index(); 
            $table->unsignedBigInteger('document_detail_id')->index(); 
            $table->string('book_type', 50)->nullable();
            $table->unsignedBigInteger('book_id')->nullable();
            $table->string('book_code', 50)->nullable();
            $table->string('document_number', 50)->nullable();
            $table->date('document_date');
            $table->string('document_status', 50)->nullable();
            $table->string('transaction_type', 50)->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->string('vendor_code', 50)->nullable();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('customer_code', 50)->nullable();


            // Item details
            $table->unsignedBigInteger('item_id')->index(); 
            $table->string('item_code', 50)->index(); 
            $table->string('item_name', 150);

            // Inventory details
            $table->unsignedBigInteger('inventory_uom_id')->index();
            $table->string('inventory_uom', 50);
            $table->decimal('receipt_qty', 15, 2)->default(0); 
            $table->decimal('issue_qty', 15, 2)->default(0);
            $table->decimal('cost_per_unit', 15, 2)->nullable();
            $table->decimal('total_cost', 20, 2)->nullable(); 
            
            //currency
            $table->unsignedBigInteger('document_currency_id')->nullable();
            $table->string('document_currency', 50)->nullable();
            $table->unsignedBigInteger('org_currency_id')->nullable();
            $table->string('org_currency_code', 50)->nullable();
            $table->decimal('org_currency_cost', 15,2)->default(0);
            $table->unsignedBigInteger('comp_currency_id')->nullable();
            $table->string('comp_currency_code', 50)->nullable();
            $table->decimal('comp_currency_cost', 15,2)->default(0);
            $table->unsignedBigInteger('group_currency_id')->nullable();
            $table->string('group_currency_code', 50)->nullable();
            $table->decimal('group_currency_cost', 15,2)->default(0);


            $table->date('original_receipt_date')->nullable();
            $table->unsignedBigInteger('utilized_id')->nullable()->index(); 
            $table->date('utilized_date')->nullable();

            $table->unsignedBigInteger('created_by')->nullable(); 
            $table->unsignedBigInteger('updated_by')->nullable(); 
            $table->unsignedBigInteger('deleted_by')->nullable(); 

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ledger');
    }
};
