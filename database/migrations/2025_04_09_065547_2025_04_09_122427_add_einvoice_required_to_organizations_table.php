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
        Schema::table('organizations', function (Blueprint $table) {
            $table->tinyInteger('einvoice_required')->default(0)->after('company_id');
            $table->string('organization_qr_code', 599)->nullable()->after('einvoice_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('einvoice_required');
            $table->dropColumn('organization_qr_code');
        });
    }
};
