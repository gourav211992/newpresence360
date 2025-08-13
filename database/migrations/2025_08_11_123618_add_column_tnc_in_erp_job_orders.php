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
        if (!Schema::hasColumn('erp_job_orders', 'tnc')) {
            Schema::table('erp_job_orders', function (Blueprint $table) {
                $table->text('tnc')->nullable()->after('remarks');
            });
        }
        // if (!Schema::hasColumn('erp_job_orders_history', 'tnc')) {
        //     Schema::table('erp_job_orders_history', function (Blueprint $table) {
        //         $table->string('tnc')->nullable()->after('remarks');
        //     });
        // }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('erp_job_orders', 'tnc')) {
            Schema::table('erp_job_orders', function (Blueprint $table) {
                $table->dropColumn('tnc');
            });
        }
        // if (Schema::hasColumn('erp_job_orders_history', 'tnc')) {
        //     Schema::table('erp_job_orders_history', function (Blueprint $table) {
        //         $table->dropColumn('tnc');
        //     });
        // }
    }
};
