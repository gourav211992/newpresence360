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
        if (Schema::hasColumn('organization_services', 'flag')) {
            return;
        }

        Schema::table('organization_services', function (Blueprint $table) {
            $table->boolean('flag')->after('icon')->default(0)->comment('0-main, 1-erp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
