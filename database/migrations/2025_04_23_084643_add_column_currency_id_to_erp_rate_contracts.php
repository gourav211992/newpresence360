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
        if (!Schema::hasColumn('erp_rate_contracts', 'currency_id')) {
            Schema::table('erp_rate_contracts', function (Blueprint $table) {
                $table->unsignedBigInteger('currency_id')->nullable()->after('vendor_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_rate_contracts', function (Blueprint $table) {
            if (Schema::hasColumn('erp_rate_contracts', 'currency_id')) {
                $table->dropColumn('currency_id');
            }
        });
    }
};
