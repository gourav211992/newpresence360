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
        if (!Schema::hasTable('erp_assign_teams')) {
        Schema::create('erp_assign_teams', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('team');
            $table->longText('remarks')->nullable();
            $table->string('legalid');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_assign_teams');
    }
};
