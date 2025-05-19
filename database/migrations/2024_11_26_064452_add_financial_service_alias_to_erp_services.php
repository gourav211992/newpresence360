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
        Schema::table('erp_services', function (Blueprint $table) {
            $table->string('financial_service_alias') -> nullable() -> after('icon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_services', function (Blueprint $table) {
            $table->dropColumn(['financial_service_alias']);
        });
    }
};
