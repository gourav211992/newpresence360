<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
    {
        // erp_customers_history changes
       Schema::table('erp_customers_history', function (Blueprint $table) {
            $table->tinyInteger('is_prospect')->default(0)->nullable()->after('revision_date');
            $table->bigInteger('product_category_id')->nullable()->unsigned()->after('is_prospect');
            $table->bigInteger('lead_source_id')->nullable()->unsigned()->after('product_category_id');
            $table->bigInteger('industry_id')->nullable()->unsigned()->after('lead_source_id');
            $table->string('lead_status', 100)->nullable()->after('industry_id');
            $table->double('sales_figure')->default(0)->after('lead_status');
            $table->string('city', 255)->nullable()->after('sales_figure');
            $table->dropColumn('on_account_required');
        });

        // erp_vendors_history changes
        Schema::table('erp_vendors_history', function (Blueprint $table) {
            $table->dropColumn('on_account_required');
            $table->json('book_codes')->nullable()->after('created_by');
        });

        // erp_items_history changes
        Schema::table('erp_items_history', function (Blueprint $table) {
            $table->bigInteger('production_route_id')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_customers_history', function (Blueprint $table) {
            $table->dropColumn([
                'is_prospect',
                'product_category_id',
                'lead_source_id',
                'industry_id',
                'lead_status',
                'sales_figure',
                'city'
            ]);
            $table->tinyInteger('on_account_required')->nullable()->after('credit_days');
        });

        Schema::table('erp_vendors_history', function (Blueprint $table) {
            $table->tinyInteger('on_account_required')->nullable()->after('credit_days');
            $table->dropColumn('book_codes');
        });

        Schema::table('erp_items_history', function (Blueprint $table) {
            $table->dropColumn('production_route_id');
        });
    }
};
