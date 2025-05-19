<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('erp_finance_fixed_asset_setup', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('organization_id'); // Organization ID
            $table->unsignedBigInteger('group_id'); // Group ID
            $table->unsignedBigInteger('company_id'); // Company ID
            $table->unsignedBigInteger('asset_category_id'); // Asset Category ID
            $table->unsignedBigInteger('ledger_id'); // Ledger ID
            $table->unsignedBigInteger('ledger_group_id'); // Ledger Group ID
            $table->integer('expected_life_years'); // Expected Life in Years
            $table->string('depreciation_method'); // Depreciation Method (SLM/WDV)
            $table->decimal('depreciation_percentage', 5, 2); // Depreciation Percentage
            $table->string('maintenance_schedule'); // Maintenance Schedule (e.g., Weekly, Monthly)
            $table->string('status'); // Status (Active/Inactive)
            $table->unsignedBigInteger('created_by'); // Created By (User ID)
            $table->string('type'); // Type
            $table->softDeletes(); // Soft delete column
            $table->timestamps(); // Created at and Updated at timestamps

            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_finance_fixed_asset_setup');
    }
};
