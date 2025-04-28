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
        Schema::table('organizations_menu', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->after('group_id')->nullable();
            $table->unsignedBigInteger('organization_id')->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations_menu', function (Blueprint $table) {
            $table->dropColumn(['company_id']);
            $table->unsignedBigInteger('organization_id')->change()->nullable(false);
        });
    }
};
