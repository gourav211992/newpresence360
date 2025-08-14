<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('erp_document_drive_actions_log', function (Blueprint $table) {
            $table->id();
            $table->enum('action', ['upload', 'download', 'move', 'rename', 'share', 'delete']);
            $table->enum('entity_type', ['file', 'folder']);
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('performed_by');
            $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
 });
    }

    public function down()
    {
        Schema::dropIfExists('erp_document_drive_actions_log');
    }
};
