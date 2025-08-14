<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->text('pan_attachment')->nullable();
            $table->text('tin_attachment')->nullable();
            $table->text('aadhar_attachment')->nullable();
            $table->text('other_documents')->nullable();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->dropColumn('pan_attachment');
            $table->dropColumn('tin_attachment');
            $table->dropColumn('aadhar_attachment');
            $table->dropColumn('other_documents');
        });
    }
};
