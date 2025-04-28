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
        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->decimal('header_discount_percentage', 15, 2)->nullable()->after('discount_amount');
            $table->decimal('header_discount_amount', 15, 2)->nullable()->after('header_discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->dropColumn('header_discount_percentage');
            $table->dropColumn('header_discount_amount');
        });
    }
};
