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
        Schema::table('erp_bom_uploads', function (Blueprint $table) {
            $table->string('consumption_per_unit')->nullable()->after('consumption_qty');
            $table->string('pieces')->nullable()->after('consumption_per_unit');
            $table->string('std_qty')->nullable()->after('pieces');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_bom_uploads', function (Blueprint $table) {
            $table->dropColumn('consumption_per_unit');
            $table->dropColumn('pieces');
            $table->dropColumn('std_qty');
        });
    }
};
