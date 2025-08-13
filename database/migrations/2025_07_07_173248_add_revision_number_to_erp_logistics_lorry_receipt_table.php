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
        Schema::table('erp_logistics_lorry_receipt', function (Blueprint $table) {
            $table->string('revision_number')->nullable()->default('0')->after('document_date');
            $table->date('revision_date')->nullable()->after('revision_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_logistics_lorry_receipt', function (Blueprint $table) {
             $table->dropColumn('revision_number', 'revision_date');
        });
    }
};
