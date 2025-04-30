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
        Schema::create('erp_einvoices', function (Blueprint $table) {
            $table->id();
            $table->morphs('morphable'); // Creates morphable_id and morphable_type
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('ack_no', 299)->nullable()->index();
            $table->dateTime('ack_date')->nullable()->index();
            $table->string('irn_number',191)->unique()->index();
            $table->longText('signed_invoice')->nullable();
            $table->longText('signed_qr_code')->nullable();
            $table->string('ewb_no', 299)->nullable()->index();
            $table->dateTime('ewb_date')->nullable()->index();
            $table->dateTime('ewb_valid_till')->nullable()->index();
            $table->string('status',191)->nullable()->index();
            $table->longText('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_einvoice_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('request_uid', 299)->index();
            $table->string('api_name',191)->nullable()->index();
            $table->string('method',99)->nullable()->index();
            $table->longText('is_error')->nullable();
            $table->longText('request_payload')->nullable();
            $table->longText('response_payload')->nullable();
            $table->string('status',191)->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_einvoice_logs');
        Schema::dropIfExists('erp_einvoices');
    }
};
