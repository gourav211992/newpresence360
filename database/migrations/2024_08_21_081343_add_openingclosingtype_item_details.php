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
        Schema::table('erp_item_details', function (Blueprint $table) {
            $table->string('opening_type')->after('opening')->comment('Dr(Debit), Cr(Credit)')->nullable();
            $table->string('closing_type')->after('closing')->comment('Dr(Debit), Cr(Credit)')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_item_details', function (Blueprint $table) {
            $table->dropColumn('opening_type');
            $table->dropColumn('closing_type');
        });
    }
};
