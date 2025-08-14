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
        Schema::table('erp_purchase_return_headers', function (Blueprint $table) {
            $table->string('qty_return_type', 199)->nullable()->after('department_id');
        });

        Schema::table('erp_purchase_return_headers_history', function (Blueprint $table) {
            $table->string('qty_return_type', 199)->nullable()->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_purchase_return_headers_history', function (Blueprint $table) {
            $table->dropColumn('qty_return_type');
        });

        Schema::table('erp_purchase_return_headers', function (Blueprint $table) {
            $table->dropColumn('qty_return_type');
        });
    }
};
