<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('erp_po_items', function (Blueprint $table) {
            $table->decimal('asn_qty', 15, 2)->nullable()->after('ge_qty');
        });

        Schema::table('erp_jo_products', function (Blueprint $table) {
            $table->decimal('asn_qty', 15, 2)->nullable()->after('updated_at');
        });

        Schema::table('erp_vendor_asn', function (Blueprint $table) {
            $table->string('asn_for')->nullable()->after('id');
            $table->unsignedBigInteger('job_order_id')->nullable()->after('purchase_order_id');
            $table->unsignedBigInteger('purchase_order_id')->nullable()->change();
        });

        Schema::table('erp_vendor_asn_items', function (Blueprint $table) {
            $table->unsignedBigInteger('po_item_id')->nullable()->change();
            $table->unsignedBigInteger('jo_prod_id')->nullable()->after('po_item_id');
        });
    }

    public function down(): void {
        Schema::table('erp_po_items', function (Blueprint $table) {
            $table->dropColumn('asn_qty');
        });

        Schema::table('erp_jo_products', function (Blueprint $table) {
            $table->dropColumn('asn_qty');
        });

        Schema::table('erp_vendor_asn', function (Blueprint $table) {
            $table->dropColumn('asn_for');
            $table->dropColumn('job_order_id');
        });

        Schema::table('erp_vendor_asn_items', function (Blueprint $table) {
            $table->dropColumn('jo_prod_id');
        });
    }
};
