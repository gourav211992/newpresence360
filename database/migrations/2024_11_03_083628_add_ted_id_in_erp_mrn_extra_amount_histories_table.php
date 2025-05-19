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
        Schema::table('erp_mrn_extra_amount_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('ted_id')->nullable()->after('ted_level');
            $table->string('ted_name',151)->nullable()->after('document_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_extra_amount_histories', function (Blueprint $table) {
            $table->dropColumn('ted_id');
            $table->dropColumn('ted_name');
        });
    }
};
