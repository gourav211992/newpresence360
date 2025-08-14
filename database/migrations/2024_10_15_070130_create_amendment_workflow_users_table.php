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
        Schema::create('erp_amendment_workflow_users', function (Blueprint $table) {
            $table->id();
            $table->integer('book_id');
            $table->integer('company_id');
            $table->integer('organization_id');
            $table->integer('user_id');
            $table->string('user_type');
            $table->unsignedBigInteger('amendment_workflow_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_amendment_workflow_users');
    }
};
