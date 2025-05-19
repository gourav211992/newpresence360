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
        Schema::create('erp_order_headers', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->nullable();
            $table->date('order_date')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_code')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->double('total_order_value')->nullable();
            $table->text('location')->nullable();
            $table->string('order_status')->nullable();
            $table->text('delivery_address')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_order_headers');
    }
};
