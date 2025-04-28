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
        Schema::table('erp_interest_rates', function (Blueprint $table) {
            $table->string('base_rate')->change();
            $table->date('effective_to')->nullable()->after('effective_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_interest_rates', function (Blueprint $table) {
            //
        });
    }
};
