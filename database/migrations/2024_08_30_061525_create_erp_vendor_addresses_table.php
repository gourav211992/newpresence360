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
        Schema::create('erp_vendor_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->index();
            $table->string('is_billing')->nullable()->index();
            $table->string('is_shipping')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_vendor_addresses');
    }
};
