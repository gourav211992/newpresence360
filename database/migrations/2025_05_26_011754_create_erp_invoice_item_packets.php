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
        Schema::create('erp_invoice_item_packets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_item_id');
            $table->unsignedBigInteger('plist_detail_id');
            $table->string('package_number');
            $table->timestamps();
        });
        Schema::create('erp_invoice_item_packets_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('invoice_item_id');
            $table->unsignedBigInteger('plist_detail_id');
            $table->string('package_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_invoice_item_packets_history');
        Schema::dropIfExists('erp_invoice_item_packets');
    }
};
