<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrganizationIdToLegalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_legals', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->after('id'); // Adjust the 'after' parameter based on your preference
            $table->unsignedBigInteger('user_id')->nullable()->after('organization_id');
            $table->string('type')->nullable()->after('user_id');
            // If you have an organizations table and want to enforce foreign key constraints, you can uncomment the following line:
            // $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_legals', function (Blueprint $table) {
            // Drop the foreign key constraint first if it exists
            // $table->dropForeign(['organization_id']);
            $table->dropColumn(['organization_id','user_id', 'type']);
        });
    }
}
