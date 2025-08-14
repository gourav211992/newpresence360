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
        Schema::create('erp_stations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('name')->nullable()->index();
            $table->string('alias')->nullable();
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('parent_id')->references('id')->on('erp_stations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_stations');
    }
};
