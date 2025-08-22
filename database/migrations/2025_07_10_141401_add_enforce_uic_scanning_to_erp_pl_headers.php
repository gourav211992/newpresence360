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
        Schema::table('erp_pl_headers', function (Blueprint $table) {
            $table->string('enforce_uic_scanning', 5)->default('no') -> index();
        });
        Schema::table('erp_pl_headers_history', function (Blueprint $table) {
            $table->string('enforce_uic_scanning', 5)->default('no') -> index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pl_headers', function (Blueprint $table) {
            $table->dropColumn(['enforce_uic_scanning']);
        });
        Schema::table('erp_pl_headers_history', function (Blueprint $table) {
            $table->dropColumn(['enforce_uic_scanning']);
        });
    }
};
