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
        Schema::table('erp_recruitment_assigned_vendors', function (Blueprint $table) {
            $table->text('remark')->nullable()->after('vendor_id');
            $table->unsignedBigInteger('created_by')->nullable()->index('created_by_index')->after('remark');
            $table->string('created_by_type')->nullable()->index('created_by_type_index')->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_recruitment_assigned_vendors', function (Blueprint $table) {
            $table->dropColumn('remark');
            $table->dropColumn('created_by');
            $table->dropColumn('created_by_type');
            $table->dropIndex('created_by_index');
            $table->dropIndex('created_by_type_index');
        }); 
    }
};
