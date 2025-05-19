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
        Schema::create('erp_legal_doc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_application_id')->constrained('erp_home_loans')->onDelete('cascade');
            $table->string('status');
            $table->string('doc');
            $table->string('remarks');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_legal_doc');
    }
};
