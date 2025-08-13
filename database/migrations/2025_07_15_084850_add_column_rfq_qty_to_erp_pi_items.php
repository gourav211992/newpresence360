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
        Schema::table('erp_pi_items', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('erp_pi_items', 'rfq_qty')) {
                $table->decimal('rfq_qty', 20, 6)->default(0)->after('mi_qty');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pi_items', function (Blueprint $table) {
            //
            if(Schema::hasColumn('erp_pi_items', 'rfq_qty')) {
                $table->dropColumn('rfq_qty');
            }
        });
    }
};
