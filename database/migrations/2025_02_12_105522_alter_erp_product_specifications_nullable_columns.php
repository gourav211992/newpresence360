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
        Schema::table('erp_product_specifications', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->change();
            $table->unsignedBigInteger('company_id')->nullable()->change();
            $table->unsignedBigInteger('organization_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_product_specifications', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable(false)->change();
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
        });
    }
};
