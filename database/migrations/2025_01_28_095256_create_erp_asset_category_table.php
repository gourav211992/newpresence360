<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('erp_asset_category', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->unsignedBigInteger('organization_id'); // Organization ID
            $table->unsignedBigInteger('group_id'); // Group ID
            $table->unsignedBigInteger('company_id'); // Company ID
            $table->string('name'); // Name of the asset category
            $table->text('description')->nullable(); // Description, nullable
            $table->unsignedBigInteger('created_by'); // Created By
            $table->string('type'); // Type
            $table->enum('status', ['active', 'inactive'])->default('active'); // Status with default value
            $table->softDeletes();
            $table->timestamps(); // Created_at and updated_at timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_asset_category');
    }
};
