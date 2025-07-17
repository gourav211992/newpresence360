<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_ledgers', function (Blueprint $table) {
            $table->string('document_status')->nullable()->after('organization_id');
            $table->integer('revision_number')->default(0)->nullable()->after('document_status');
            $table->integer('approval_level')->default(1)->nullable()->after('revision_number');
            $table->unsignedBigInteger('created_by')->nullable()->after('approval_level');
        });
    }

    public function down()
    {
        Schema::table('erp_ledgers', function (Blueprint $table) {
            $table->dropColumn([
                'document_status',
                'revision_number',
                'approval_level',
                'created_by',
            ]);
        });
    }

};
