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
        Schema::table('erp_recovery_loans', function (Blueprint $table) {
            $table->json('dis_detail')->nullable()->after('dis_id'); // Add dis_detail field after dis_id
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_recovery_loans', function (Blueprint $table) {
            $table->dropColumn('dis_detail');
            //
        });
    }
};
