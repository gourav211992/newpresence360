<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('erp_vendor_portal_books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('book_id');
            $table->foreign('vendor_id')->references('id')->on('erp_vendors');
            $table->foreign('service_id')->references('id')->on('erp_services');
            $table->foreign('book_id')->references('id')->on('erp_books');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_vendor_portal_books');
    }
};
