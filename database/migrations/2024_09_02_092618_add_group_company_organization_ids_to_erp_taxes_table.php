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
        Schema::table('erp_taxes', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->after('description')->index();
            $table->unsignedBigInteger('company_id')->nullable()->after('group_id')->index();
            $table->unsignedBigInteger('organization_id')->nullable()->after('company_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_taxes', function (Blueprint $table) {
            $table->dropColumn(['group_id', 'company_id', 'organization_id']);
        });
    }
};
