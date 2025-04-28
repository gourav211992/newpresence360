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
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->integer('approval_level')->after('document_status')->nullable(); // Adjust 'some_column' to the actual column you want to place it after.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->dropColumn('approval_level');
        });
    }
};
