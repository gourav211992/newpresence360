<?php

// database/migrations/XXXX_XX_XX_XXXXXX_create_item_details_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('erp_item_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voucher_id')->nullable()->index();
            $table->unsignedBigInteger('ledger_id')->nullable()->index();
            $table->decimal('debit_amt', 15, 2)->default(0);
            $table->decimal('credit_amt', 15, 2)->default(0);
            $table->unsignedBigInteger('cost_center_id')->nullable()->index();
            $table->string('notes')->nullable();

            $table->unsignedBigInteger('group_id')->index();
            $table->unsignedBigInteger('company_id')->index(); 
            $table->unsignedBigInteger('organization_id')->index(); 
            
            $table->timestamps();

            // Foreign keys
            $table->foreign('voucher_id')->references('id')->on('erp_vouchers')->onDelete('cascade');
            $table->foreign('ledger_id')->references('id')->on('erp_ledgers')->onDelete('cascade');
            $table->foreign('cost_center_id')->references('id')->on('erp_cost_centers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_item_details');
    }
}
