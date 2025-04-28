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
        Schema::table('erp_vendor_items', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->after('id')->nullable();
            $table->unsignedBigInteger('company_id')->after('group_id')->nullable();
            $table->unsignedBigInteger('organization_id')->after('company_id')->nullable();
        });
        Schema::table('erp_customer_items', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->after('id')->nullable();
            $table->unsignedBigInteger('company_id')->after('group_id')->nullable();
            $table->unsignedBigInteger('organization_id')->after('company_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_vendor_items', function (Blueprint $table) {
            $table->dropColumn(['group_id','company_id','organization_id']);
        });
        Schema::table('erp_customer_items', function (Blueprint $table) {
            $table->dropColumn(['group_id','company_id','organization_id']);
        });
    }
};
