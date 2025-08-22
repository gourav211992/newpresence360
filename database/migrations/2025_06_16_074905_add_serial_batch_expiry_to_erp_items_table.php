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
            $table->boolean('is_serial_no')->default(false)->after('storage_volume');
            $table->boolean('is_batch_no')->default(false)->after('is_serial_no');
            $table->boolean('is_expiry')->default(false)->after('is_batch_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropColumn(['is_serial_no', 'is_batch_no', 'is_expiry']);
        });
    }
};
