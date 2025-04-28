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
            $table->integer('revision_number')->default(0)->after('approvalStatus');
            $table->date('revision_date')->nullable()->after('revision_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->dropColumn('revision_number');
            $table->dropColumn('revision_date');
        });
    }
};
