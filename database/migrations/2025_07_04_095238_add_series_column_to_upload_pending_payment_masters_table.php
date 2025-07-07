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
        Schema::table('upload_pending_payment_masters', function (Blueprint $table) {
            //
            $table->string('series')->nullable()->after('doc_type'); // Adjust position as needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upload_pending_payment_masters', function (Blueprint $table) {
            //
            $table->dropColumn('series');
        });
    }
};
