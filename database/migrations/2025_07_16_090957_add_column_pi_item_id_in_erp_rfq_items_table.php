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
        Schema::table('erp_rfq_items', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('erp_rfq_items', 'pi_item_id')) {
                $table->json('pi_item_ids')->nullable()->after('request_qty');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_rfq_items', function (Blueprint $table) {
            //
            if (Schema::hasColumn('erp_rfq_items', 'pi_item_id')) {
                $table->dropColumn('pi_item_ids');
            }
        });
    }
};
