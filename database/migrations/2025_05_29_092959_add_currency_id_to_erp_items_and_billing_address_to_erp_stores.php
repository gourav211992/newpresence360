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
        Schema::table('erp_items', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->after('hsn_id');
        });

        Schema::table('erp_stores', function (Blueprint $table) {
            $table->boolean('billing_address')->default(0)->after('contact_email'); // 0 = no, 1 = yes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropColumn('currency_id');
        });

        Schema::table('erp_stores', function (Blueprint $table) {
            $table->dropColumn('billing_address');
        });
    }
};
