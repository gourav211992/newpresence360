<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL to rename column
        DB::statement("ALTER TABLE `erp_logistics_lorry_receipt` CHANGE `status` `document_status` VARCHAR(255)");

        Schema::table('erp_logistics_lorry_receipt', function (Blueprint $table) {
            $table->integer('approval_level')
                  ->default(1)
                  ->after('document_status')
                  ->comment('current approval level');
        });
    }

    public function down(): void
    {
        Schema::table('erp_logistics_lorry_receipt', function (Blueprint $table) {
            $table->dropColumn('approval_level');
        });

        // Rename back using raw SQL
        DB::statement("ALTER TABLE `erp_logistics_lorry_receipt` CHANGE `document_status` `status` VARCHAR(255)");
    }
};
