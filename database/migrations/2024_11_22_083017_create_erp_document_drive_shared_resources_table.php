<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('erp_document_drive_shared_resources', function (Blueprint $table) {
            $table->id();
            $table->enum('entity_type', ['file', 'folder']);
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('shared_with');
            $table->enum('permissions', ['view', 'share', 'rename', 'download', 'move', 'delete']);
            $table->unsignedBigInteger('shared_by');
            $table->timestamps(0);

        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_document_drive_shared_resources');
    }
};
