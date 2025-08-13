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
        Schema::table('erp_bank_statements', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->index('created_by_index')->after('date');
            $table->string('created_by_type')->nullable()->index('created_by_type_index')->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_bank_statements', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('created_by_type');
            $table->dropIndex('created_by_index');
            $table->dropIndex('created_by_type_index');
        });
    }
};
