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
        Schema::create('erp_hsns', function (Blueprint $table) {
            $table->id(); 
            $table->enum('type',ConstantHelper::HSN_CODE_TYPE)->default(ConstantHelper::HSN);
            $table->string('code')->unique()->index();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_hsns');
    }
};
