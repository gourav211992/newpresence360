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
        Schema::table('erp_land_leases', function (Blueprint $table) {
            $table->double('invoice_security_deposit', 15,2) -> default(0) -> after('security_deposit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_land_leases', function (Blueprint $table) {
            $table->removeColumns(['invoice_security_deposit']);
        });
    }
};
