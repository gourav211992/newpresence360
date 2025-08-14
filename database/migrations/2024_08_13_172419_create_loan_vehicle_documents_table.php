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
        Schema::create('erp_loan_vehicle_documents', function (Blueprint $table) {
            $table->id();
            $table->integer('vehicle_id');
            $table->string('adhar_card')->nullable();
            $table->string('pan_gir_no')->nullable();
            $table->string('vehicle_doc')->nullable();
            $table->string('security_doc')->nullable();
            $table->string('partnership_doc')->nullable();
            $table->string('affidavit_doc')->nullable();
            $table->string('scan_doc')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_loan_vehicle_documents');
    }
};
