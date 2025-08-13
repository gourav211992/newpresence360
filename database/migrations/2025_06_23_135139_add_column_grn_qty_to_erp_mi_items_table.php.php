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
        Schema::table('erp_mi_items', function (Blueprint $table) {
            $table->double('grn_qty', 20, 6) -> after('issue_qty') -> default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mi_items', function (Blueprint $table) {
            $table->dropColumn('grn_qty');
        });
    }
};
