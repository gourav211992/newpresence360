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
        if (Schema::hasColumn('erp_stores', 'billing_address')) {
            Schema::table('erp_stores', function (Blueprint $table) {
                $table->dropColumn('billing_address');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_stores', function (Blueprint $table) {
            
        });
    }
};
