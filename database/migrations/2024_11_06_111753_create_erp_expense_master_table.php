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
        Schema::create('erp_expense_master', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); 
            $table->string('alias')->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->boolean('is_purchase')->default(false)->index(); 
            $table->boolean('is_sale')->default(false)->index(); 
            $table->foreignId('expense_ledger_id')->nullable()->constrained('erp_ledgers')->onDelete('cascade');
            $table->foreignId('service_provider_ledger_id')->nullable()->constrained('erp_ledgers')->onDelete('cascade');            
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE);
            $table->unsignedInteger('group_id')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedInteger('organization_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_expense_master');
    }
};
