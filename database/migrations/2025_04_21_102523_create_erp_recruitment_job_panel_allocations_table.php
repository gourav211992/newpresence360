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
        Schema::create('erp_recruitment_job_panel_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id')->nullable()->index();
            $table->unsignedBigInteger('round_id')->nullable()->index();
            $table->unsignedBigInteger('panel_id')->nullable();
            $table->string('external_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_recruitment_job_panel_allocations');
    }
};
