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
        Schema::create('erp_vouchers_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->integer('voucher_no');
            $table->string('voucher_name');
            $table->unsignedBigInteger('book_type_id');
            $table->unsignedBigInteger('book_id');
            $table->date('date');
            $table->decimal('amount', 15, 2);
            $table->string('document')->nullable();
            $table->string('remarks')->nullable();
            $table->integer('approvalLevel')->default(1);
            $table->string('approvalStatus');

            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('organization_id');

            $table->morphs('voucherable');
            $table->integer('revision_number')->default(0);
            $table->date('revision_date')->nullable();
        
            $table->timestamps();

            $table->foreign('book_type_id')->references('id')->on('erp_book_types')->onDelete('cascade');
            $table->foreign('book_id')->references('id')->on('erp_books')->onDelete('cascade');
        });

        Schema::create('erp_item_details_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('voucher_id')->nullable()->index();
            $table->unsignedBigInteger('ledger_id')->nullable()->index();
            $table->decimal('debit_amt', 15, 2)->default(0);
            $table->decimal('credit_amt', 15, 2)->default(0);
            $table->unsignedBigInteger('cost_center_id')->nullable()->index();
            $table->string('notes')->nullable();
            $table->date('date')->default(date('Y-m-d'));

            $table->decimal('opening', 15, 2)->nullable();
            $table->string('opening_type')->comment('Dr(Debit), Cr(Credit)')->nullable();
            $table->decimal('closing', 15, 2)->nullable();
            $table->string('closing_type')->comment('Dr(Debit), Cr(Credit)')->nullable();

            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('organization_id');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_vouchers_history');
        Schema::dropIfExists('erp_item_details_history');
    }
};
