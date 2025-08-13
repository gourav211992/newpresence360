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
        Schema::create('erp_insp_checklists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->unsignedBigInteger('detail_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('checklist_id');
            $table->string('checklist_name')->nullable();
            $table->unsignedBigInteger('checklist_detail_id');
            $table->string('name');
            $table->string('value')->nullable();
            $table->string('result');
            $table->timestamps();
        });

        Schema::create('erp_insp_checklists_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('header_id');
            $table->unsignedBigInteger('detail_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('checklist_id');
            $table->string('checklist_name')->nullable();
            $table->unsignedBigInteger('checklist_detail_id');
            $table->string('name');
            $table->string('value')->nullable();
            $table->string('result');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_insp_checklists_history');
        Schema::dropIfExists('erp_insp_checklists');
    }
};
