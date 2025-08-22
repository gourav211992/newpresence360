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
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->string('document_type') -> change();
        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->string('document_type') -> change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->string('document_type') -> change();
        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->string('document_type') -> change();
        });
    }
};
