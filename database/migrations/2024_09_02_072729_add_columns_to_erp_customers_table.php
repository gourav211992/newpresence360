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
        Schema::table('erp_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('industry_id')->nullable()->index()->after('organization_id');
            $table->boolean('is_prospect')->default(false)->after('industry_id');
            $table->string('lead_status', 100)->nullable()->index()->after('is_prospect');
            $table->double('sales_figure')->default(0)->after('lead_status');
            $table->unsignedBigInteger('erp_product_category_id')->nullable()->index()->after('sales_figure');
            $table->unsignedBigInteger('erp_lead_source_id')->nullable()->index()->after('erp_product_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_customers', function (Blueprint $table) {
            $table->dropColumn(['group_id', 'company_id', 'organization_id']);
        });
    }
};
