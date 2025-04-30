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
            $table->unsignedBigInteger('eway_bill_master_id')->nullable()->after('supplier_invoice_date');
            $table->string('transportation_mode',191)->nullable()->after('eway_bill_master_id');
            $table->string('ewb_number',191)->nullable()->after('transportation_mode');
            $table->string('transporter_name',191)->nullable()->after('ewb_number');
            $table->string('vehicle_no',191)->nullable()->after('ewb_number');
            $table->tinyInteger('is_irn_generated')->default(0)->after('total_amount');
            $table->tinyInteger('is_ewb_generated')->default(0)->after('is_irn_generated');
        });

        Schema::table('erp_purchase_return_headers_history', function (Blueprint $table) {
            $table->unsignedBigInteger('eway_bill_master_id')->nullable()->after('supplier_invoice_date');
            $table->string('transportation_mode',191)->nullable()->after('eway_bill_master_id');
            $table->string('ewb_number',191)->nullable()->after('transportation_mode');
            $table->string('transporter_name',191)->nullable()->after('ewb_number');
            $table->string('vehicle_no',191)->nullable()->after('ewb_number');
            $table->tinyInteger('is_irn_generated')->default(0)->after('total_amount');
            $table->tinyInteger('is_ewb_generated')->default(0)->after('is_irn_generated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_purchase_return_headers_history', function (Blueprint $table) {
            $table->dropColumn(
                [
                    'eway_bill_master_id', 
                    'transportation_mode',
                    'ewb_number', 
                    'transporter_name',
                    'vehicle_no',
                    'is_irn_generated',
                    'is_ewb_generated'
                ]
            );
        });

        Schema::table('erp_purchase_return_headers', function (Blueprint $table) {
            $table->dropColumn(
                [
                    'eway_bill_master_id', 
                    'transportation_mode',
                    'ewb_number', 
                    'transporter_name',
                    'vehicle_no',
                    'is_irn_generated',
                    'is_ewb_generated'
                ]
            );
        });
    }
};
