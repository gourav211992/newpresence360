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
        Schema::table('erp_loan_settlements', function (Blueprint $table) {
            $table->integer('approval_level')->default(1)->comment('Current Approval Level')->after('status');
            $table->string('document_status')->after('approval_level');
   });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_loan_settlements', function (Blueprint $table) {
            $table->integer('approval_level')->default(1)->comment('Current Approval Level');
            $table->string('document_status');

        });
    }
};
