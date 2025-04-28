<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('erp_document_drive_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('created_by');
            $table->string('created_by_type')->default('user'); // User type
            $table->json('tags')->nullable();
            $table->timestamps(0);
            $table->softDeletes();

        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_document_drive_folders');
    }
};
