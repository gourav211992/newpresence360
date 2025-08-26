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
        Schema::table('erp_ledgers', function (Blueprint $table) {
            //DECIMAL(10,2)
            $table->decimal('tcs_without_pan', 10, 2)->nullable()->after('tcs_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_ledgers', function (Blueprint $table) {
            //
        });
    }
};
