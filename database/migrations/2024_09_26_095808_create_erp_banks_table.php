<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
    public function up()
    {
        Schema::create('erp_banks', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name')->nullable(); 
            $table->string('bank_code')->nullable(); 
            $table->unsignedBigInteger('company_id')->nullable(); 
            $table->unsignedBigInteger('organization_id')->nullable(); 
            $table->unsignedBigInteger('group_id')->nullable();
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE)->nullable()->index(); 
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_banks');
    }
};
