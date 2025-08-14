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
        Schema::create('erp_document_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('document_type');
            $table->unsignedBigInteger('document_id');
            $table->integer('revision_number')->default(0);
            $table->string('approval_type')->comment('Approve / Reject / Amend');
            $table->unsignedBigInteger('user_id')->comment('User ID of Approver');
            $table->date('approval_date');
            $table->text('remarks')->nullable();
            //$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_document_approvals');
    }
};
