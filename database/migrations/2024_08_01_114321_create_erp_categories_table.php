<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Helpers\ConstantHelper;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('erp_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('type')->nullable();
            $table->string('name')->nullable()->index();
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('parent_id')->references('id')->on('erp_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        DB::statement('ALTER TABLE erp_categories DROP FOREIGN KEY erp_categories_parent_id_foreign');
        
        // Drop the table
        Schema::dropIfExists('erp_categories');
    }
};
