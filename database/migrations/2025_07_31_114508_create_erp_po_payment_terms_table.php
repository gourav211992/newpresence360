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
        Schema::create('erp_po_payment_terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('po_header_id');
            $table->unsignedBigInteger('payment_term_id');
            $table->unsignedBigInteger('payment_term_detail_id');
            $table->integer('credit_days')->default(0);
            $table->decimal('percent', 5, 2)->nullable();
            $table->enum('trigger_type', ConstantHelper::TRIGGER_TYPES)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_po_payment_terms');
    }
};
