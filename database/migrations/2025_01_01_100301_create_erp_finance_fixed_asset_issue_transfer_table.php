<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('erp_finance_fixed_asset_issue_transfer', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('organization_id'); // Organization ID
            $table->unsignedBigInteger('group_id'); // Group ID
            $table->unsignedBigInteger('company_id'); // Company ID
            $table->unsignedBigInteger('asset_id'); // Asset ID
            $table->string('status', 50); // Status of the transfer
            $table->string('location', 255); // Current location
            $table->string('transfer_location', 255); // Transfer location
            $table->unsignedBigInteger('authorized_person'); // Authorized person ID
            $table->unsignedBigInteger('created_by'); // Created By
            $table->string('type'); // Type
            $table->timestamps(); // created_at and updated_at timestamps

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_finance_fixed_asset_issue_transfer');
    }
};
