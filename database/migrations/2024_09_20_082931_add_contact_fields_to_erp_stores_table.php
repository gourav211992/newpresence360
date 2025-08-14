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
        Schema::table('erp_stores', function (Blueprint $table) {
            $table->string('contact_person')->after('company_id')->nullable(); 
            $table->string('contact_phone_no', 20)->after('store_name')->nullable(); 
            $table->string('contact_email')->after('contact_phone_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_stores', function (Blueprint $table) {
            $table->dropColumn(['contact_person', 'contact_phone_no', 'contact_email_id']);
        });
    }
};
