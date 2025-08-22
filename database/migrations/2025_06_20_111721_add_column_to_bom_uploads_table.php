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
        Schema::table('erp_bom_uploads', function (Blueprint $table) {
            $table->string('customer_id')->nullable()->after('vendor_name');
            $table->string('customer_code')->nullable()->after('customer_id');
            $table->string('customer_name')->nullable()->after('customer_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_bom_uploads', function (Blueprint $table) {
            $table->dropColumn(['customer_id', 'customer_code', 'customer_name']);
        });
    }
};
