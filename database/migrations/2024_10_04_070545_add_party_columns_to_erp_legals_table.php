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
        Schema::table('erp_legals', function (Blueprint $table) {
            $table->string('party_type');
            $table->string('party_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_legals', function (Blueprint $table) {
            $table->dropColumn(['party_type', 'party_name']);
        });
    }
};
