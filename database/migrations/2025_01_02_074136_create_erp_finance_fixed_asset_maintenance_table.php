<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('erp_finance_fixed_asset_maintenance', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('organization_id'); // Organization ID
            $table->unsignedBigInteger('group_id'); // Group ID
            $table->unsignedBigInteger('company_id'); // Company ID
            $table->unsignedBigInteger('asset_id'); // Asset ID
            $table->date('verf_date');
            $table->string('condition', 100);
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('created_by'); // Created By
            $table->string('type'); // Type
            $table->timestamps(); // created_at and updated_at timestamps
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_finance_fixed_asset_maintenance');
    }
};
