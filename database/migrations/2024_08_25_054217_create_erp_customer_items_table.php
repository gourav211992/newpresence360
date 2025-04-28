<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_customer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('item_code')->nullable()->index();
            $table->string('item_name')->nullable()->index();
            $table->string('part_number')->nullable();
            $table->text('item_details')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('item_id')->references('id')->on('erp_items')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('erp_customers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('erp_customer_items', function (Blueprint $table) {
            Schema::dropIfExists('erp_customer_items');
        });

    }
};
