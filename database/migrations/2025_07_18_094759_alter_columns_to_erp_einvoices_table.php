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
        Schema::table('erp_einvoices', function (Blueprint $table) {
            $table->string('type')->nullable();
            $table->string('irn_number')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_einvoices', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->string('irn_number')->nullable(false)->change();
        });
    }
};
