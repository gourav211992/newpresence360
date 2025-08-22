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
        Schema::create('erp_drivers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreignId('user_id')->nullable();

            $table->string('name', 100);
            $table->string('email', 150)->nullable();
            $table->string('mobile_no', 20); // Includes country codes if needed

            $table->integer('experience_years')->default(0);

            $table->string('license_no', 30)->unique(); // Typically license numbers are <= 20–30 chars
            $table->date('license_expiry_date')->nullable();

            // Media references
            $table->unsignedBigInteger('license_front')->nullable()->comment("erp_driver_media's id");
            $table->unsignedBigInteger('license_back')->nullable()->comment("erp_driver_media's id");
            $table->unsignedBigInteger('id_proof_front')->nullable()->comment("erp_driver_media's id");
            $table->unsignedBigInteger('id_proof_back')->nullable()->comment("erp_driver_media's id");

            $table->timestamps();
            $table->softDeletes(); // Add soft delete support
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_drivers');
    }
};
