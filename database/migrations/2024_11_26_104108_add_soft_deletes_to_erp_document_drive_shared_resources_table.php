<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_document_drive_shared_resources', function (Blueprint $table) {
            $table->softDeletes(); // Adds the `deleted_at` column
        });
    }

    public function down()
    {
        Schema::table('erp_document_drive_shared_resources', function (Blueprint $table) {
            $table->dropColumn('deleted_at'); // Removes the `deleted_at` column
        });
    }
};
