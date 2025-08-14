<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('erp_land_plots', function (Blueprint $table) {
            $table->string('document_status')->nullable()->after('status');
            $table->integer('approval_level')->default(0)->after('document_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_land_plots', function (Blueprint $table) {
            $table->dropColumn(['document_status', 'approval_level']);
        });
    }
};
