<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

class CreateErpTaxDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_tax_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_id')->nullable()->constrained('erp_taxes')->onDelete('cascade');
            $table->foreignId('ledger_id')->nullable()->constrained('erp_ledgers')->onDelete('cascade');
            $table->string('tax_code')->nullable();
            $table->string('tax_name')->nullable();
            $table->enum('tax_type', ConstantHelper::TAX_TYPES)->nullable();
            $table->decimal('tax_percentage', 5, 2);
            $table->enum('place_of_supply', ConstantHelper::PLACE_OF_SUPPLY_TYPES)->nullable();
            $table->boolean('is_purchase')->default(false);
            $table->boolean('is_sale')->default(false);
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE)->nullable();
            $table->timestamps();
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
        Schema::dropIfExists('erp_tax_details');
    }
}
