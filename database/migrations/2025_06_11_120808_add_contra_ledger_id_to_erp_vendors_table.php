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
        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->unsignedBigInteger('contra_ledger_id')->nullable()->after('related_party');
        });

        Schema::table('erp_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('contra_ledger_id')->nullable()->after('related_party');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->dropColumn('contra_ledger_id');
        });

        Schema::table('erp_customers', function (Blueprint $table) {
            $table->dropColumn('contra_ledger_id');
        });
    }
};
