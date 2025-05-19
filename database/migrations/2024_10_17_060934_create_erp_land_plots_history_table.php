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
        Schema::create('erp_land_plots_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('series_id');
            $table->string('document_no');
            $table->unsignedBigInteger('land_id');
            $table->decimal('land_size', 10, 2);
            $table->string('land_location');
            $table->tinyInteger('status')->default(1);
            $table->string('khasara_no')->nullable();
            $table->decimal('plot_area', 10, 2);
            $table->string('area_unit');
            $table->string('dimension')->nullable();
            $table->decimal('plot_valuation', 12, 2)->nullable();
            $table->string('address')->nullable();
            $table->string('pincode')->nullable();
            $table->string('type_of_usage');
            $table->text('remarks')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('geofence_file')->nullable();
            $table->string('organization_id');
            $table->string('user_id');
            $table->string('type')->nullable();
            $table->string('plot_name')->nullable();
            $table->json('attachments')->nullable();
            $table->string('landable_type');
            $table->unsignedBigInteger('landable_id');
            $table->integer('approvalLevel')->default(0);
            $table->string('approvalStatus')->default('completed');
            $table->integer('revision_number')->default(0);
            $table->date('revision_date')->nullable();
            $table->text('appr_rej_recom_remark')->nullable();
            $table->string('appr_rej_doc')->nullable();
            $table->string('appr_rej_behalf_of')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_land_plots_history');
    }
};
