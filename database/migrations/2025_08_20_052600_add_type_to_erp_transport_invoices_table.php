<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('erp_transport_invoices', function (Blueprint $table) {
           $table->string('type')->default(1)->after('id');
            // You can change the type to enum/int/etc. if needed
        });
    }

    public function down(): void
    {
        Schema::table('erp_transport_invoices', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
