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
        Schema::create('erp_legals', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('issues');
            $table->integer('series');
            $table->string('requestno');
            $table->string('name');
            $table->string('phone', 15);
            $table->string('email');
            $table->string('subject');
            $table->string('file_path')->nullable();
            $table->longText('remark')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_legals');
    }
};
