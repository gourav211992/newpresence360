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
        Schema::create('erp_pi_po_mapping', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pi_id');
            $table->unsignedBigInteger('pi_item_id');
            $table->unsignedBigInteger('po_id')->nullable();
            $table->unsignedBigInteger('po_item_id')->nullable();
            $table->double('po_qty',18,6)->default(0.00);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_pi_po_mapping');
    }
};
