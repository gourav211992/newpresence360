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
        Schema::table('erp_amendment_workflows', function (Blueprint $table) {
            if (Schema::hasColumn('erp_amendment_workflows', 'min_value')) {
                $table->dropColumn('min_value');
            }
            if (Schema::hasColumn('erp_amendment_workflows', 'max_value')) {
                $table->dropColumn('max_value');
            }

            $table->unsignedBigInteger('user_id')->change();            
            $table->string('user_type')->nullable()->after('user_id');
            $table->unsignedBigInteger('book_level_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_amendment_workflows', function (Blueprint $table) {
            $table->integer('min_value');
            $table->integer('max_value');
            $table->json('user_id')->change();            
            $table->dropColumn('user_type');
            $table->dropColumn('book_level_id');
        });
    }
};

