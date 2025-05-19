<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
    public function up()
    {
        Schema::create('erp_discount_master', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); 
            $table->string('alias')->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->unsignedBigInteger('discount_ledger_id')->nullable();
            $table->foreign('discount_ledger_id')->references('id')->on('erp_ledgers')->onDelete('set null');
            $table->boolean('is_purchase')->default(false)->index(); 
            $table->boolean('is_sale')->default(false)->index(); 
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE);
            $table->unsignedInteger('group_id')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedInteger('organization_id')->nullable();
            $table->softDeletes();
            $table->timestamps();  
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_discount_master');
    }
};
