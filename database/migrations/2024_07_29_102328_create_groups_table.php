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
        Schema::create('erp_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedBigInteger('parent_group_id')->nullable();
            $table->string('status');

            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('organization_id');
            
            $table->timestamps();

            $table->foreign('parent_group_id')->references('id')->on('erp_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_groups');
    }
};
