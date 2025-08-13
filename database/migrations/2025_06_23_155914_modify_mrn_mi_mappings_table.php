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
        Schema::table('mrn_mi_mappings', function (Blueprint $table) {
            $table->unsignedBigInteger('jo_id') -> nullable();
            $table->dropColumn(['supplier_qty']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mrn_mi_mappings', function (Blueprint $table) {
            $table->dropColumn(['jo_id']);
            $table->double('supplier_qty', 20, 6)->default(0);
        });
    }
};
