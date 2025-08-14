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
    public function up()
    {
        Schema::create('upload_vendor_masters', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable(); 
            $table->string('vendor_initial')->nullable(); 
            $table->string('vendor_code')->nullable();
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable(); 
            $table->string('currency')->nullable(); 
            $table->string('payment_term')->nullable(); 
            $table->string('vendor_type')->nullable(); 
            $table->string('vendor_sub_type')->nullable();
            $table->string('organization_type')->nullable(); 
            $table->string('vendor_code_type')->nullable();  
            $table->string('country')->nullable(); 
            $table->string('state')->nullable(); 
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('pin_code')->nullable(); 
            $table->string('email')->nullable(); 
            $table->string('phone')->nullable(); 
            $table->string('mobile')->nullable(); 
            $table->string('whatsapp_number')->nullable(); 
            $table->string('notification_mode')->nullable(); 
            $table->string('pan_number')->nullable(); 
            $table->string('tin_number')->nullable(); 
            $table->string('aadhar_number')->nullable();
            $table->string('ledger_code')->nullable(); 
            $table->string('ledger_group')->nullable(); 
            $table->decimal('credit_limit', 15, 2)->nullable(); 
            $table->integer('credit_days')->nullable(); 
            $table->string('gst_applicable')->nullable(); 
            $table->string('gstin_no')->nullable();
            $table->string('gst_registered_name')->nullable(); 
            $table->string('gstin_registration_date')->nullable(); 
            $table->string('tds_applicable')->nullable();
            $table->string('wef_date')->nullable();
            $table->string('tds_certificate_no')->nullable(); 
            $table->decimal('tds_tax_percentage', 5, 2)->nullable(); 
            $table->string('tds_category')->nullable(); 
            $table->decimal('tds_value_cab', 15, 2)->nullable(); 
            $table->string('tan_number')->nullable();
            $table->string('msme_registered')->nullable(); 
            $table->string('msme_no')->nullable(); 
            $table->string('msme_type')->nullable(); 
            $table->string('status')->nullable();
            $table->unsignedBigInteger('group_id')->nullable(); 
            $table->unsignedBigInteger('company_id')->nullable(); 
            $table->unsignedBigInteger('organization_id')->nullable(); 
            $table->text('remarks')->nullable(); 
            $table->string('batch_no')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); 
            $table->timestamps();
            $table->softDeletes();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_vendor_masters');
    }
};
