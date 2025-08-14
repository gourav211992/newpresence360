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
        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->decimal('pr_rejected_qty', 15,6)->nullable()->after('rejected_qty');
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->decimal('pr_rejected_qty', 15,6)->nullable()->after('rejected_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->dropColumn('pr_rejected_qty');
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->dropColumn('pr_rejected_qty');
        });
    }
};
