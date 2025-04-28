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
        Schema::create('loan_logs', function (Blueprint $table) {
            $table->id();
            $table->string('series_id');
            $table->string('application_number');
            $table->foreignId('loan_application_id')->constrained('erp_home_loans');
            $table->unsignedBigInteger('organization_id');
            $table->string('module_type');
            $table->string('description');
            $table->string('created_by');
            $table->string('document');
            $table->string('user_type');
            $table->integer('revision_number')->default(0);
            $table->string('revision_date');
            $table->string('active_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_logs');
    }
};
