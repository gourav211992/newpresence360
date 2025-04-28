<?php

use App\Helpers\ConstantHelper;
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
        Schema::table('erp_boms', function (Blueprint $table) {
            $table->enum('customizable', ['yes','no'])->default('no')->after('type');
            $table->enum('bom_type', ConstantHelper::BOM_TYPES)->default(ConstantHelper::FIXED)->after('customizable');
        });
        Schema::table('erp_boms_history', function (Blueprint $table) {
            $table->enum('customizable', ['yes','no'])->default('no')->after('type');
            $table->enum('bom_type', ConstantHelper::BOM_TYPES)->default(ConstantHelper::FIXED)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_boms_history', function (Blueprint $table) {
            $table->dropColumn('customizable');
            $table->dropColumn('bom_type');
        });
        Schema::table('erp_boms', function (Blueprint $table) {
            $table->dropColumn('customizable');
            $table->dropColumn('bom_type');
        });
    }
};
