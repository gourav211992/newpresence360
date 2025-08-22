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
        if (!Schema::hasColumn('erp_psv_headers', 'remarks') && Schema::hasColumn('erp_psv_headers', 'remark')) {
            DB::statement("ALTER TABLE erp_psv_headers CHANGE remark remarks VARCHAR(255)");
        }
        if (!Schema::hasColumn('erp_psv_items', 'adjusted_qty')) {
            Schema::table('erp_psv_items', function (Blueprint $table) {
            $table->decimal('adjusted_qty', 20, 6)->default(0)->after('verified_qty');
            });
        }
        if (!Schema::hasColumn('erp_psv_items', 'rate')) {
            Schema::table('erp_psv_items', function (Blueprint $table) {
                $table->decimal('rate', 20, 6)->default(0)->after('adjusted_qty');
            });
        }
        if (!Schema::hasColumn('erp_psv_items', 'total_amount')) {
            Schema::table('erp_psv_items', function (Blueprint $table) {
            $table->decimal('total_amount', 20, 6)->default(0)->after('rate');
            });
        }
        if (!Schema::hasColumn('erp_rate_contracts', 'revision_date')) {
            Schema::table('erp_rate_contracts', function (Blueprint $table) {
            $table->date('revision_date')->nullable()->after('revision_number');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        if (!Schema::hasColumn('erp_psv_headers', 'remark') && Schema::hasColumn('erp_psv_headers', 'remarks')) {
            // Revert the column name back
            DB::statement("ALTER TABLE erp_psv_headers CHANGE remarks remark VARCHAR(255)");
        }
        if (Schema::hasColumn('erp_psv_items', 'adjusted_qty')) {
            Schema::table('erp_psv_items', function (Blueprint $table) {
            $table->dropColumn('adjusted_qty');
            
            });
        }
        if (Schema::hasColumn('erp_psv_items', 'rate')) {
            Schema::table('erp_psv_items', function (Blueprint $table) {
                $table->dropColumn('rate');
            });
        }
        if (Schema::hasColumn('erp_psv_items', 'total_amount')) {
            Schema::table('erp_psv_items', function (Blueprint $table) {
                $table->dropColumn('total_amount');
            });
        }
        if (Schema::hasColumn('erp_rate_contracts', 'revision_date')) {
            Schema::table('erp_rate_contracts', function (Blueprint $table) {
            $table->dropColumn('revision_date');
            });
        }
    }
};
