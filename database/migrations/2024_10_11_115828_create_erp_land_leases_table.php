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
        Schema::create('erp_land_leases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type')->nullable();
            //$table->unsignedBigInteger('land_id')->nullable();

            $table->unsignedBigInteger('series_id'); // Series foreign key
            $table->string('document_no');
            $table->date('document_date');
            $table->string('reference_no')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->integer('exchage_rate')->nullable();
            $table->text('billing_address')->nullable();
            $table->string('agreement_no')->nullable();
            $table->integer('lease_time')->nullable(false)->comment('Lease duration in years');
            $table->date('lease_start_date')->nullable();
            $table->date('lease_end_date')->nullable();
            $table->enum('repayment_period_type', ['monthly', 'quarterly', 'yearly'])->nullable();
            $table->integer('repayment_period')->nullable();
            $table->integer('security_deposit')->nullable();
            $table->boolean('deposit_refundable')->default(0);
            $table->integer('processing_fee')->nullable();
            $table->integer('lease_increment')->nullable()->comment('Lease increment in percenteage');
            $table->integer('lease_increment_duration')->nullable()->comment('Lease increment duration in years');
            $table->integer('grace_period')->nullable()->comment('Grace period in days');
            $table->integer('late_fee')->nullable()->comment('Lease late fee in percenteage');
            $table->integer('late_fee_value')->nullable()->comment('Lease late fee in value');
            $table->integer('late_fee_duration')->nullable()->comment('late fee duration in days');
            $table->decimal('sub_total_amount', 15, 2)->nullable()->default(0.00);
            $table->decimal('extra_charges', 15, 2)->nullable()->default(0.00);
            $table->decimal('tax_amount', 15, 2)->nullable()->default(0.00);
            $table->decimal('total_amount', 15, 2)->nullable()->default(0.00);
            $table->text('remarks')->nullable();

            $table->morphs('leaseable');
            $table->integer('approvalLevel')->default(0);
            $table->string('approvalStatus')->default('completed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_land_leases');
    }
};
