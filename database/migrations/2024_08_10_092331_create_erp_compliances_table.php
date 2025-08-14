<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

class CreateErpCompliancesTable extends Migration
{
    public function up()
    {
        Schema::create('erp_compliances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id')->nullable()->index(); 
            $table->boolean('tds_applicable')->default(false);
            $table->date('wef_date')->nullable();
            $table->string('tds_certificate_no')->nullable()->index();
            $table->decimal('tds_tax_percentage', 14, 2)->nullable();
            $table->string('tds_category')->nullable();
            $table->decimal('tds_value_cab', 14, 2)->nullable();
            $table->string('tan_number')->nullable()->index();
            $table->boolean('gst_applicable')->default(false);
            $table->string('gstin_no')->nullable()->index();
            $table->string('gst_registered_name')->nullable();
            $table->date('gstin_registration_date')->nullable();
            $table->boolean('msme_registered')->default(false);
            $table->string('msme_no')->nullable()->index();
            $table->enum('msme_type', ConstantHelper::MSME_TYPES)->default(ConstantHelper::MICRO)->nullable();
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE);
            //$table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->morphs('morphable');
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_compliances');
    }
}
