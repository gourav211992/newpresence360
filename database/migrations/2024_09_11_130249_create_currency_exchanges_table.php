<?php

use App\Helpers\ConstantHelper;
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
        Schema::create('erp_currency_exchanges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('from_currency_id')->comment('erp_currency id')->nullable();
            $table->unsignedBigInteger('upto_currency_id')->comment('erp_currency id')->nullable();
            $table->date('from_date')->nullable();
            $table->date('upto_date')->nullable();
            $table->decimal('exchange_rate', 15, 6)->default(0.000000);
            $table->foreign('from_currency_id')->references('id')->on('erp_currency')->onDelete('cascade');
            $table->foreign('upto_currency_id')->references('id')->on('erp_currency')->onDelete('cascade');
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE)->index();
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
        Schema::dropIfExists('erp_currency_exchanges');
    }
};
