<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('dep_method', 50)->nullable();
            $table->string('dep_type')->after('dep_method')->nullable();
            $table->decimal('dep_percentage', 10, 2)->after('dep_type')->nullable();
        });
    }

    public function down()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['dep_method', 'dep_type', 'dep_percentage']);
        });
    }
};
