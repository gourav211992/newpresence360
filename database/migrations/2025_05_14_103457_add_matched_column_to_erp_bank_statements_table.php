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
            $table->unsignedBigInteger('account_id')->nullable()->index()->after('bank_id');
            $table->boolean('matched')->nullable()->index()->after('account_id');
            $table->string('uid','200')->nullable()->index()->after('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_bank_statements', function (Blueprint $table) {
            $table->dropColumn('account_id');
            $table->dropColumn('matched');
            $table->dropColumn('uid');
        });
    }
};
