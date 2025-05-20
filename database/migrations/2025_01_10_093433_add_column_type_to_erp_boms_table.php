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
        Schema::table('erp_boms', function (Blueprint $table) {
            $table->string('type')->default(ConstantHelper::BOM_SERVICE_ALIAS)->after('id');
        });
        Schema::table('erp_boms_history', function (Blueprint $table) {
            $table->string('type')->default(ConstantHelper::BOM_SERVICE_ALIAS)->after('source_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_boms', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('erp_boms_history', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
