<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErpLandParcelsHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_land_parcels_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('series_id');
            $table->string('document_no');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('surveyno')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('khasara_no')->nullable();
            $table->decimal('plot_area', 10, 2);
            $table->string('area_unit')->nullable();
            $table->string('dimension')->nullable();
            $table->decimal('land_valuation', 12, 2)->nullable();
            $table->string('address')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('pincode')->nullable();
            $table->text('remarks')->nullable();
            $table->longText('service_item')->nullable()->charset('utf8mb4')->collation('utf8mb4_bin')->check('json_valid(service_item)');
            $table->longText('attachments')->nullable()->charset('utf8mb4')->collation('utf8mb4_bin')->check('json_valid(attachments)');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->date('handoverdate')->nullable();
            $table->string('organization_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('type')->nullable();
            $table->string('landable_type');
            $table->unsignedBigInteger('landable_id');
            $table->integer('approvalLevel')->default(0);
            $table->string('approvalStatus')->default('completed');
            $table->integer('revision_number')->default(0);
            $table->date('revision_date')->nullable();
            $table->string('active_status')->nullable();
            $table->string('active_remarks')->nullable();
            $table->text('appr_rej_recom_remark')->nullable();
            $table->string('appr_rej_doc')->nullable();
            $table->string('appr_rej_behalf_of')->nullable();

            $table->timestamps(); // when the history record was created

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_land_parcels_history');
    }
}
