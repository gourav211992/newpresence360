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
        Schema::create('temp_erp_customer_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index()->nullable();
            $table->string('customer_code', 255)->index();
            $table->unsignedBigInteger('organization_id')->index();
            $table->string('channel_partner_name', 255)->nullable();
            $table->string('location_code', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('sales_rep_code', 255)->nullable();
            $table->double('ly_sale')->nullable();
            $table->double('cy_sale')->nullable();
            $table->double('apr')->nullable();
            $table->double('may')->nullable();
            $table->double('jun')->nullable();
            $table->double('jul')->nullable();
            $table->double('aug')->nullable();
            $table->double('sep')->nullable();
            $table->double('oct')->nullable();
            $table->double('nov')->nullable();
            $table->double('dec')->nullable();
            $table->double('jan')->nullable();
            $table->double('feb')->nullable();
            $table->double('mar')->nullable();
            $table->string('year', 255);
            $table->decimal('total_target', 10, 2)->default(0);
            $table->unsignedBigInteger('created_by')->index()->nullable();
            $table->unsignedBigInteger('updated_by')->index()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_erp_customer_targets');
    }
};
