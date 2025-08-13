<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->string('lot_number',191)->nullable()->after('document_number');
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_id');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->string('lot_number',191)->nullable()->after('document_number');
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_id');
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_id');
            $table->string('sub_store_code',191)->nullable()->after('store_code');
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_id');
            $table->string('sub_store_code',191)->nullable()->after('store_code');
        });

        Schema::table('erp_purchase_return_headers', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_id');
        });

        Schema::table('erp_purchase_return_headers_history', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_id');
        });

        Schema::table('erp_purchase_return_details', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_id');
            $table->string('sub_store_code',191)->nullable()->after('store_code');
        });

        Schema::table('erp_purchase_return_details_history', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_store_id')->nullable()->after('store_id');
            $table->string('sub_store_code',191)->nullable()->after('store_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_purchase_return_details_history', function (Blueprint $table) {
            $table->dropColumn(['sub_store_id','sub_store_code']);
        });

        Schema::table('erp_purchase_return_details', function (Blueprint $table) {
            $table->dropColumn(['sub_store_id','sub_store_code']);
        });

        Schema::table('erp_purchase_return_headers_history', function (Blueprint $table) {
            $table->dropColumn('sub_store_id');
        });

        Schema::table('erp_purchase_return_headers', function (Blueprint $table) {
            $table->dropColumn('sub_store_id');
        });

        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            $table->dropColumn(['sub_store_id','sub_store_code']);
        });

        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->dropColumn(['sub_store_id','sub_store_code']);
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            $table->dropColumn([
                'lot_number',
                'sub_store_id'
            ]);
        });

        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            $table->dropColumn([
                'lot_number',
                'sub_store_id'
            ]);
        });
    }
};
