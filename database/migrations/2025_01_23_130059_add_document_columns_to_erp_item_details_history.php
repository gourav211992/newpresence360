<?php 
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
        Schema::table('erp_item_details_history', function (Blueprint $table) {
            $table->bigInteger('ledger_parent_id')->nullable();
            $table->double('debit_amt_org')->nullable();
            $table->double('credit_amt_org')->nullable();
            $table->double('debit_amt_comp')->nullable();
            $table->double('credit_amt_comp')->nullable();
            $table->double('debit_amt_group')->nullable();
            $table->double('credit_amt_group')->nullable();
            $table->string('entry_type', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->timestamp('deleted_at')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_item_details_history', function (Blueprint $table) {
            $table->dropColumn('ledger_parent_id');
            $table->dropColumn('debit_amt_org');
            $table->dropColumn('credit_amt_org');
            $table->dropColumn('debit_amt_comp');
            $table->dropColumn('credit_amt_comp');
            $table->dropColumn('debit_amt_group');
            $table->dropColumn('credit_amt_group');
            $table->dropColumn('entry_type');
            $table->dropColumn('remarks');
            $table->dropColumn('deleted_at');
        });
    }
};