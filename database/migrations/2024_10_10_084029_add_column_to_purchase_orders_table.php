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
        // Adding to erp_purchase_orders table
        if (!Schema::hasColumn('erp_purchase_orders', 'attachment')) {
            Schema::table('erp_purchase_orders', function (Blueprint $table) {
                $table->json('attachment')->nullable();
            });
        }

        // Adding to erp_purchase_orders_history table
        if (!Schema::hasColumn('erp_purchase_orders_history', 'attachment')) {
            Schema::table('erp_purchase_orders_history', function (Blueprint $table) {
                $table->json('attachment')->nullable();
            });
        }

        // Adding to erp_boms table
        if (!Schema::hasColumn('erp_boms', 'attachment')) {
            Schema::table('erp_boms', function (Blueprint $table) {
                $table->json('attachment')->nullable();
            });
        }

        // Adding to erp_boms_history table
        if (!Schema::hasColumn('erp_boms_history', 'attachment')) {
            Schema::table('erp_boms_history', function (Blueprint $table) {
                $table->json('attachment')->nullable();
            });
        }

        // Adding to erp_purchase_indents table
        if (!Schema::hasColumn('erp_purchase_indents', 'attachment')) {
            Schema::table('erp_purchase_indents', function (Blueprint $table) {
                $table->json('attachment')->nullable();
            });
        }

        // Adding to erp_purchase_indents_history table
        if (!Schema::hasColumn('erp_purchase_indents_history', 'attachment')) {
            Schema::table('erp_purchase_indents_history', function (Blueprint $table) {
                $table->json('attachment')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_purchase_orders_history', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });

        Schema::table('erp_boms', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });
        Schema::table('erp_boms_history', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });

        Schema::table('erp_purchase_indents', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });
        Schema::table('erp_purchase_indents_history', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });
    }
};
