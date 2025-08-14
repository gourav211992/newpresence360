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
        Schema::table('erp_items', function (Blueprint $table) {
            $table->string('storage_type')->nullable()->after('service_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropColumn('storage_type');
        });
    }
};
