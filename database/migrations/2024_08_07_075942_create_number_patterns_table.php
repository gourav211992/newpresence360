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
        Schema::create('erp_number_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('erp_books')->onDelete('cascade');
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('company_id');
            $table->string('series_numbering');
            $table->string('reset_pattern');
            $table->string('prefix');
            $table->string('suffix');
            $table->integer('starting_no');
            $table->timestamps();
            // $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            // $table->foreign('company_id')->references('id')->on('organization_companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_number_patterns');
    }
};
