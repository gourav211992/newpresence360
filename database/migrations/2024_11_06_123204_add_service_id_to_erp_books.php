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
        Schema::table('erp_books', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->after('org_service_id')->nullable()->comment('ID of erp_services');
            $table->unsignedBigInteger('org_service_id')->change()->comment('ID of erp_organization_services');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_books', function (Blueprint $table) {
            $table->dropColumn(['service_id']);
        });
    }
};
