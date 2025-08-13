<?php

use App\Helpers\ConstantHelper;
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
        Schema::dropIfExists('erp_machine_details');
        Schema::dropIfExists('erp_machines');
        Schema::create('erp_machines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('production_route_id')->nullable();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('attribute_group_id')->nullable();
            $table->string('attribute_group_name')->nullable();
            $table->enum('status', ['active','inactive'])->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_machine_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_id')->nullable();
            $table->unsignedBigInteger('attribute_group_id')->nullable();
            $table->string('attribute_group_name')->nullable();
            $table->unsignedBigInteger('attribute_id')->nullable();
            $table->string('attribute_value')->nullable();
            $table->double('length',20,4)->default(0);
            $table->double('width',20,4)->default(0);
            $table->double('no_of_pairs',20,4)->default(0);
            $table->timestamps();
        });
        Schema::table('erp_mo_products', function (Blueprint $table) {
            $table->unsignedBigInteger('machine_id')->nullable()->after('so_item_id');
            $table->double('number_of_sheet', 20, 6)->default(0)->after('machine_id');
        });
        Schema::table('erp_mfg_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('machine_id')->nullable()->after('is_last_station');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mfg_orders', function (Blueprint $table) {
            $table->dropColumn('machine_id');
        });
        Schema::table('erp_mo_products', function (Blueprint $table) {
            $table->dropColumn('machine_id');
            $table->dropColumn('number_of_sheet');
        });
        Schema::dropIfExists('erp_machine_details');
        Schema::dropIfExists('erp_machines');
    }
};
