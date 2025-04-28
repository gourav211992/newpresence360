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
        Schema::create('erp_vendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_type_id')->nullable()->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('subcategory_id')->nullable()->index();
            $table->unsignedBigInteger('currency_id')->nullable()->index();
            $table->unsignedBigInteger('payment_terms_id')->nullable()->index();
            $table->string('vendor_code')->unique()->index();
            $table->enum('vendor_type', [ConstantHelper::VENDOR_TYPES])->nullable();
            $table->string('company_name')->nullable();
            $table->string('display_name')->nullable();
            $table->enum('related_party', ConstantHelper::STOP_OPTIONS)->default('no');
            $table->string('email')->nullable();
            $table->string('phone')->nullable(); 
            $table->string('mobile')->nullable(); 
            $table->string('whatsapp_number')->nullable(); 
            $table->json('notification')->nullable(); 
            $table->string('pan_number')->nullable(); 
            $table->string('tin_number')->nullable(); 
            $table->string('aadhar_number')->nullable(); 
            $table->decimal('opening_balance', 15, 2)->nullable();
            $table->string('pricing_type')->nullable();
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->integer('credit_days')->nullable();
            $table->decimal('interest_percent', 10, 2)->nullable();
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE);
            $table->enum('stop_billing', ConstantHelper::STOP_OPTIONS)->default(ConstantHelper::NO);
            $table->enum('stop_purchasing', ConstantHelper::STOP_OPTIONS)->default(ConstantHelper::NO);
            $table->enum('stop_payment', ConstantHelper::STOP_OPTIONS)->default(ConstantHelper::NO);
            $table->foreign('category_id')->references('id')->on('erp_categories')->onDelete('cascade');
            $table->foreign('subcategory_id')->references('id')->on('erp_categories')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('erp_currency')->onDelete('cascade');
            $table->foreign('payment_terms_id')->references('id')->on('erp_payment_terms')->onDelete('cascade');
            $table->foreign('organization_type_id')->references('id')->on('erp_organization_types')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_vendors');
    }

   
};
