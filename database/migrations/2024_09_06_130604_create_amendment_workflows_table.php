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
        Schema::create('erp_amendment_workflows', function (Blueprint $table) {
            $table->id();
            $table->integer('book_id');
            $table->integer('company_id');
            $table->integer('organization_id');
            $table->json('user_id');
            $table->decimal('min_value', 15, 2)->default(0);
            $table->decimal('max_value', 15, 2)->default(0);
            $table->timestamps(); // Timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_amendment_workflows');
    }
};
