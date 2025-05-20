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
        Schema::create('erp_supply_splits', function (Blueprint $table) {
            $table->id();
            $table->integer('organization_id')->index();
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('customer_code',100)->index();
            $table->unsignedBigInteger('supply_partner_id')->index();
            $table->double('supply_percentage')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_supply_splits');
    }
};
