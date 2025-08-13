<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // erp_customers_history
        Schema::create('erp_customers_history', function (Blueprint $table) {
            // Primary and Reference
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id')->comment('Reference to original record');
        
            // Categorization
            $table->unsignedBigInteger('organization_type_id')->nullable()->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('subcategory_id')->nullable()->index();
        
            // Ledger & Payment Info
            $table->unsignedBigInteger('currency_id')->nullable()->index();
            $table->unsignedBigInteger('payment_terms_id')->nullable()->index();
            $table->unsignedBigInteger('ledger_group_id')->nullable()->index();
            $table->unsignedBigInteger('ledger_id')->nullable()->index();
            $table->unsignedBigInteger('contra_ledger_id')->nullable();
            $table->unsignedBigInteger('reld_customer_id')->nullable();
            $table->unsignedBigInteger('sales_person_id')->nullable();
        
            // Book Info
            $table->integer('book_id')->nullable();
            $table->string('book_code', 255)->nullable();
        
            // Customer Codes & Identity
            $table->string('customer_code_type', 255)->nullable();
            $table->string('customer_code', 255)->nullable()->index();
            $table->string('customer_type', 255)->nullable();
            $table->string('customer_initial', 255)->nullable();
            $table->string('company_name', 255)->nullable();
            $table->string('display_name', 255)->nullable();
            $table->string('legal_name', 255)->nullable();
        
            // GST & Tax Info
            $table->string('taxpayer_type', 255)->nullable();
            $table->string('gst_status', 255)->nullable();
            $table->string('block_status', 255)->nullable();
            $table->date('deregistration_date')->nullable();
            $table->unsignedBigInteger('gst_state_id')->nullable();
        
            // PAN/TIN/Aadhar
            $table->string('pan_number', 255)->nullable();
            $table->string('tin_number', 255)->nullable();
            $table->string('aadhar_number', 255)->nullable();
        
            // Attachments
            $table->text('pan_attachment')->nullable();
            $table->text('tin_attachment')->nullable();
            $table->text('aadhar_attachment')->nullable();
            $table->text('other_documents')->nullable();
        
            // Contact Info
            $table->string('email', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->string('mobile', 255)->nullable();
            $table->string('whatsapp_number', 255)->nullable();
            $table->longText('notification')->nullable();
        
            // Financial Info
            $table->decimal('opening_balance', 15, 2)->nullable();
            $table->string('pricing_type', 255)->nullable();
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->integer('credit_days')->nullable();
            $table->tinyInteger('on_account_required')->nullable();
            $table->decimal('interest_percent', 10, 2)->nullable();
        
            // Status & Flags
            $table->enum('related_party', ['Yes', 'No'])->default('No');
            $table->string('status', 255)->nullable();
            $table->string('document_status', 255)->nullable();
            $table->integer('approval_level')->default(1);
            $table->string('revision_number', 255)->default('0');
            $table->timestamp('revision_date')->nullable();
            $table->enum('stop_billing', ['Yes', 'No'])->default('No');
            $table->enum('stop_purchasing', ['Yes', 'No'])->default('No');
            $table->enum('stop_payment', ['Yes', 'No'])->default('No');
        
            // Address Info
            $table->longText('customer_address')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('customer_pincode', 10)->nullable();
        
            // Organizational Info (Keep together)
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('enter_company_org_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
        
            // Audit Info
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
        
        // erp_vendors_history
        Schema::create('erp_vendors_history', function (Blueprint $table) {
            // Primary and Reference
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id')->nullable();
        
            // Categorization
            $table->unsignedBigInteger('organization_type_id')->nullable()->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('subcategory_id')->nullable()->index();
        
            // Ledger & Payment Info
            $table->unsignedBigInteger('currency_id')->nullable()->index();
            $table->unsignedBigInteger('payment_terms_id')->nullable()->index();
            $table->unsignedBigInteger('ledger_group_id')->nullable()->index();
            $table->unsignedBigInteger('ledger_id')->nullable()->index();
            $table->unsignedBigInteger('contra_ledger_id')->nullable();
            $table->unsignedBigInteger('reld_vendor_id')->nullable();
        
            // Book Info
            $table->integer('book_id')->nullable();
            $table->string('book_code', 255)->nullable();
        
            // Vendor Code & Type
            $table->string('vendor_code_type', 255)->nullable();
            $table->string('vendor_code', 255)->nullable()->index();
            $table->string('vendor_type', 255)->nullable();
            $table->string('vendor_sub_type', 255)->nullable();
        
            // Name & Identity
            $table->string('company_name', 255)->nullable();
            $table->string('vendor_initial', 255)->nullable();
            $table->string('display_name', 255)->nullable();
            $table->string('legal_name', 255)->nullable();
        
            // GST/Tax Info
            $table->string('taxpayer_type', 255)->nullable();
            $table->string('gst_status', 255)->nullable();
            $table->string('block_status', 255)->nullable();
            $table->date('deregistration_date')->nullable();
            $table->string('pan_number', 255)->nullable();
            $table->string('tin_number', 255)->nullable();
            $table->string('aadhar_number', 255)->nullable();
            $table->unsignedBigInteger('gst_state_id')->nullable();
        
            // Attachments
            $table->text('pan_attachment')->nullable();
            $table->text('tin_attachment')->nullable();
            $table->text('aadhar_attachment')->nullable();
            $table->text('other_documents')->nullable();
        
            // Contact Info
            $table->string('email', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->string('mobile', 255)->nullable();
            $table->string('whatsapp_number', 255)->nullable();
            $table->longText('notification')->nullable();
        
            // Financial Info
            $table->decimal('opening_balance', 15, 2)->nullable();
            $table->string('pricing_type', 255)->nullable();
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->integer('credit_days')->nullable();
            $table->tinyInteger('on_account_required')->nullable();
            $table->decimal('interest_percent', 10, 2)->nullable();
        
            // Status & Flags
            $table->enum('related_party', ['Yes', 'No'])->default('No');
            $table->string('status', 255)->nullable();
            $table->string('document_status', 255)->nullable();
            $table->integer('approval_level')->default(1);
            $table->string('revision_number', 255)->default('0');
            $table->timestamp('revision_date')->nullable();
            $table->enum('stop_billing', ['Yes', 'No'])->default('No');
            $table->enum('stop_purchasing', ['Yes', 'No'])->default('No');
            $table->enum('stop_payment', ['Yes', 'No'])->default('No');
        
            // Organizational Info
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('enter_company_org_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
        
            // Audit Info
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        // erp_vendor_portal_books_history
        Schema::create('erp_vendor_portal_books_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->unsignedBigInteger('service_id')->nullable()->index();
            $table->integer('book_id')->nullable();
        });

        // erp_vendor_portal_users_history
        Schema::create('erp_vendor_portal_users_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id')->nullable();
            
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status', 50)->nullable()->index(); 
        
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        // erp_vendor_stores_history
        Schema::create('erp_vendor_stores_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable()->index();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_customers_history');
        Schema::dropIfExists('erp_vendors_history');
        Schema::dropIfExists('erp_vendor_portal_books_history');
        Schema::dropIfExists('erp_vendor_portal_users_history');
        Schema::dropIfExists('erp_vendor_stores_history');
    }
};
