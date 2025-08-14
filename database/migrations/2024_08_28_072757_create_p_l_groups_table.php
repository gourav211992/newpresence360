<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('erp_p_l_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('group_ids')->default(new Expression('(JSON_ARRAY())'));
            $table->unsignedBigInteger('company_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_p_l_groups');
    }
};
