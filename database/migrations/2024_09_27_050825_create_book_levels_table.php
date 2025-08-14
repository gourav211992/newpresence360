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
        Schema::create('erp_book_levels', function (Blueprint $table) {
            $table->id();
            $table->integer('book_id');
            $table->integer('level');
            $table->decimal('min_value', 15, 2)->default(0);
            $table->decimal('max_value', 15, 2);
            $table->enum('rights', ['anyone', 'all']);
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('organization_id');
            $table->timestamps();
        });

        if (!Schema::hasColumn('erp_approval_workflows', 'min_value')) {
            return;
        }

        Schema::table('erp_approval_workflows', function (Blueprint $table) {
            $table->unsignedBigInteger('book_level_id');
            $table->dropColumn('level');
            $table->dropColumn('min_value');
            $table->dropColumn('max_value');
            $table->dropColumn('rights');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_book_levels');
    }
};
