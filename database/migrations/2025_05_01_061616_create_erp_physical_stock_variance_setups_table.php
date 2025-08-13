<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

class CreateErpPhysicalStockVarianceSetupsTable extends Migration
{
    public function up()
    {
        
        Schema::create('erp_physical_stock_variance_accounts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('group_id')->nullable();
            $table->integer('company_id')->nullable();
            $table->unsignedInteger('organization_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('sub_category_id')->nullable()->index();
            $table->json('item_id')->nullable();
            $table->json('book_id')->nullable();
            $table->unsignedBigInteger('ledger_id')->nullable()->index();
            $table->unsignedBigInteger('ledger_group_id')->nullable()->index();
            $table->foreign('ledger_id')->references('id')->on('erp_ledgers')->onDelete('cascade');
            $table->foreign('ledger_group_id')->references('id')->on('erp_groups')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('erp_categories')->onDelete('cascade');
            $table->foreign('sub_category_id')->references('id')->on('erp_categories')->onDelete('cascade');
            $table->string('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_physical_stock_variance_accounts');
    }
}