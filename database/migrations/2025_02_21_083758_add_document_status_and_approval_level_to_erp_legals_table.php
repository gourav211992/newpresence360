<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_legals', function (Blueprint $table) {
            $table->string('document_status', 50)->default('pending')->after('some_column'); // Adjust column position
            $table->integer('approval_level')->default(0)->after('document_status');
        });
    }

    public function down()
    {
        Schema::table('erp_legals', function (Blueprint $table) {
            $table->dropColumn(['document_status', 'approval_level']);
        });
    }
};
