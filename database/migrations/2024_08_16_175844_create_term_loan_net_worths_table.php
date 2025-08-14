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
        Schema::create('erp_term_loan_net_worths', function (Blueprint $table) {
            $table->id();
            $table->integer('term_loan_id');
            $table->string('name')->nullable();
            $table->string('father_name')->nullable();
            $table->date('dob')->nullable();
            $table->string('unit_address1')->nullable();
            $table->string('unit_address2')->nullable();
            $table->string('unit_phone')->nullable();
            $table->string('resi_address1')->nullable();
            $table->string('resi_address2')->nullable();
            $table->string('resi_mobile')->nullable();
            $table->string('resi_phone')->nullable();
            $table->string('qualification')->nullable();
            $table->string('present_profession')->nullable();
            $table->integer('passport_holder')->nullable();
            $table->string('permanent_acc')->nullable();
            $table->string('income_declare')->nullable();
            $table->string('bank_address')->nullable();
            $table->string('opening_bank_date')->nullable();
            $table->integer('club_member')->nullable();
            $table->string('club_name')->nullable();
            $table->string('club_address')->nullable();
            $table->string('paid_membership_fee')->nullable();
            $table->string('cash_on_hold')->nullable();
            $table->string('bank_cash')->nullable();
            $table->string('bank_investment')->nullable();
            $table->string('bank_deposit')->nullable();
            $table->string('bank_shares')->nullable();
            $table->string('jewelery')->nullable();
            $table->string('moveable_asset')->nullable();
            $table->string('moveable_sub_total')->nullable();
            $table->string('moveable_total')->nullable();
            $table->string('total_net_worth')->nullable();
            $table->string('asset_proof')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_term_loan_net_worths');
    }
};
