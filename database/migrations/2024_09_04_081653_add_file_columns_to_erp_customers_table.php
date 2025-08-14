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
        Schema::table('erp_customers', function (Blueprint $table) {
            $table->text('pan_attachment')->nullable();
            $table->text('tin_attachment')->nullable();
            $table->text('aadhar_attachment')->nullable();
            $table->text('other_documents')->nullable();
            $table->longText('customer_address')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('customer_pincode',10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_customers', function (Blueprint $table) {
            $table->dropColumn('pan_attachment');
            $table->dropColumn('tin_attachment');
            $table->dropColumn('aadhar_attachment');
            $table->dropColumn('other_documents');
            $table->dropColumn('customer_address');
            $table->dropColumn('customer_pincode');
        });
    }
};
