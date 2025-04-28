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
    public function up()
    {
        Schema::create('erp_bank_infos', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name')->nullable();
            $table->string('beneficiary_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('re_enter_account_number')->nullable()->index();
            $table->string('ifsc_code')->nullable()->index();
            $table->boolean('primary')->default(false);
            $table->morphs('morphable');
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_bank_infos');
    }
};
