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
        //
        Schema::create('erp_transporter_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            // $table->string('transporter_request_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id')->nullable();
            $table->string('book_code')->nullable();
            $table->string('document_number')->nullable();
            $table->enum('doc_number_type', ['Auto', 'Manually'])->default('Manually');
            $table->enum('doc_reset_pattern', ['Never', 'Yearly', 'Quarterly', 'Monthly'])->nullable();
            $table->string('doc_prefix')->nullable();
            $table->string('doc_suffix')->nullable();
            $table->date('document_date')->nullable();
            $table->unsignedBigInteger('doc_no')->nullable();
            $table->dateTime('loading_date_time')->nullable();
            $table->string('revision_number')->default('0');
            $table->date('revision_date')->nullable();
            $table->integer('approval_level')->default(1)->comment('current approval level');
            $table->string('document_status')->nullable()->comment('completed,shortlisted,closed');
            $table->string('vehicle_type')->nullable();
            $table->decimal('total_weight',15,2)->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_code')->nullable();
            $table->dateTime('bid_start')->nullable();
            $table->dateTime('bid_end')->nullable();
            $table->json('transporter_ids')->nullable();
            $table->text('remarks')->nullable();


            $table->unsignedBigInteger('selected_bid_id')->nullable();


            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->json('attachment')->nullable();
            $table->foreign('book_id')->references('id')->on('erp_books');
        });
        Schema::create('erp_transporter_request_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transporter_request_id')->nullable();
            $table->unsignedBigInteger('address_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('location_name')->nullable();
            $table->enum('location_type', ['pick_up','drop_off'])->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('transporter_request_id')->references('id')->on('erp_transporter_requests');
            $table->foreign('location_id')->references('id')->on('erp_stores');
            $table->foreign('address_id')->references('id')->on('erp_addresses');

        });
        Schema::create('erp_transporter_request_bids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transporter_request_id')->comment('id of erp_transporter_requests');
            $table->unsignedBigInteger('transporter_id')->nullable();
            $table->decimal('bid_price',15,2)->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_contact_no')->nullable();
            $table->text('transporter_remarks')->nullable();
            $table->enum('bid_status', ['submitted','shortlisted','confirmed','cancelled'])->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('transporter_request_id')->references('id')->on('erp_transporter_requests');
            $table->foreign('transporter_id')->references('id')->on('erp_vendors');
        });

        //TRANSPORTER DETAILS
        
        //vehicle type 
        Schema::create('erp_vehicle_types',function(Blueprint $table){
            $table->id();
            $table->string('vehicle_type')->nullable();
            $table->decimal('capacity',15,6)->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->enum('status',['active','inactive'])->default('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('erp_transporter_request_bid_details');
        Schema::dropIfExists('erp_transporter_request_locations');
        Schema::dropIfExists('erp_transporter_requests');
        Schema::dropIfExists('erp_vehicle_types');

    }
};
