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
        // For 'item' table
        Schema::table('erp_items', function (Blueprint $table) {
            $table->string(column: 'document_status')->nullable()->after('status');
            $table->integer('approval_level')->default(1)->after('document_status');
            $table->string('revision_number')->default('0')->after('approval_level');
            $table->timestamp('revision_date')->nullable()->after('revision_number');
        });

        // For 'customer' table
        Schema::table('erp_customers', function (Blueprint $table) {
            $table->string('document_status')->nullable()->after('status');
            $table->integer('approval_level')->default(1)->after('document_status');
            $table->string('revision_number')->default('0')->after('approval_level');
            $table->timestamp('revision_date')->nullable()->after('revision_number');
        });

        // For 'vendor' table
        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->string('document_status')->nullable()->after('status');
            $table->integer('approval_level')->default(1)->after('document_status');
            $table->string('revision_number')->default('0')->after('approval_level');
            $table->timestamp('revision_date')->nullable()->after('revision_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropColumn(['document_status', 'approval_level', 'revision_number','revision_date']);
        });
        Schema::table('erp_customers', function (Blueprint $table) {
            $table->dropColumn(['document_status', 'approval_level', 'revision_number','revision_date']);
        });
        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->dropColumn(['document_status', 'approval_level', 'revision_number','revision_date']);
        });
    }
};
