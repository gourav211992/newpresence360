<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_document_drive_shared_resources', function (Blueprint $table) {
            // Add file_id and folder_id as foreign keys
            $table->foreignId('file_id')->nullable();
            $table->foreignId('folder_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('erp_document_drive_shared_resources', function (Blueprint $table) {
            // Drop file_id and folder_id columns if rolling back
            $table->dropColumn('file_id');
            $table->dropColumn('folder_id');
        });
    }

};
