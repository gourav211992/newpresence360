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
        Schema::create('erp_term_loan_addresses', function (Blueprint $table) {
            $table->id();
            $table->integer('term_loan_id');
            $table->string('co_term')->nullable();
            $table->string('street_road')->nullable();
            $table->string('house_land_mark')->nullable();
            $table->string('city_town_village')->nullable();
            $table->string('pin_code')->nullable();
            $table->string('registered_offc_tele')->nullable();
            $table->string('registered_offc_mobile')->nullable();
            $table->string('registered_offc_email_id')->nullable();
            $table->string('registered_offc_fax_num')->nullable();
            $table->string('addr1')->nullable();
            $table->string('addr2')->nullable();
            $table->string('tele_no')->nullable();
            $table->string('factory_tele')->nullable();
            $table->string('factory_mobile')->nullable();
            $table->string('factory_email_id')->nullable();
            $table->string('factory_fax_num')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_term_loan_addresses');
    }
};
