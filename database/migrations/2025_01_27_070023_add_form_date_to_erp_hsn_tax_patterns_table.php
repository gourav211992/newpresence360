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
        Schema::table('erp_hsn_tax_patterns', function (Blueprint $table) {
            $table->date('from_date')->after('upto_price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_hsn_tax_patterns', function (Blueprint $table) {
            $table->dropColumn('from_date');
        });
    }
};
