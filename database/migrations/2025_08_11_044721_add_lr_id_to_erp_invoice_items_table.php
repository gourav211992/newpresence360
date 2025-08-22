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
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_invoice_items', 'lr_id')) {
                $table->string('lr_id')->nullable()->after('land_lease_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('erp_invoice_items', function (Blueprint $table) {
            $table->dropColumn('lr_id');
        });
    }
};
