<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_document_drive_folders', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->after('tags');
        });
    }

    public function down()
    {
        Schema::table('erp_document_drive_folders', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
