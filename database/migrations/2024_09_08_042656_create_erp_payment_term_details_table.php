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
        Schema::create('erp_payment_term_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_term_id')->index();
            $table->string('installation_no')->nullable();
            $table->integer('term_days')->nullable();
            $table->decimal('percent', 5, 2)->nullable();
            $table->enum('trigger_type', ConstantHelper::TRIGGER_TYPES)->nullable();
            $table->foreign('payment_term_id')->references('id')->on('erp_payment_terms')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_payment_term_details');
    }
};
