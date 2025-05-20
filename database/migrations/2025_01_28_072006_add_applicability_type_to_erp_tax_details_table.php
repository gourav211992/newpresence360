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
        Schema::table('erp_tax_details', function (Blueprint $table) {
            $table->enum('applicability_type', ConstantHelper::TAX_APPLICATION_TYPE)->default(ConstantHelper::COLLECTION);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_tax_details', function (Blueprint $table) {
            $table->dropColumn('applicability_type');
        });
    }
};
