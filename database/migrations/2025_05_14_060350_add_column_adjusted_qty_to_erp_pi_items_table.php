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
        Schema::table('erp_pi_so_mapping', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id')->nullable()->after('bom_detail_id');
        });
        Schema::table('erp_pi_items_history', function (Blueprint $table) {
            $table->double('required_qty', 20,6)->default(0)->after('vendor_name');
            $table->double('adjusted_qty' ,20,6)->default(0)->after('required_qty');
        });
        Schema::table('erp_pi_items', function (Blueprint $table) {
            $table->double('required_qty', 20,6)->default(0)->after('vendor_name');
            $table->double('adjusted_qty' ,20,6)->default(0)->after('required_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pi_so_mapping', function (Blueprint $table) {
            $table->dropColumn('vendor_id');
        });
        Schema::table('erp_pi_items_history', function (Blueprint $table) {
            $table->dropColumn('required_qty');
            $table->dropColumn('adjusted_qty');
        });
        Schema::table('erp_pi_items', function (Blueprint $table) {
            $table->dropColumn('required_qty');
            $table->dropColumn('adjusted_qty');
        });
    }
};
