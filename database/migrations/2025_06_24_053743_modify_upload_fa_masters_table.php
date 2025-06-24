<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUploadFaMastersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the table if it exists
        Schema::dropIfExists('upload_fa_masters');

        // Recreate the table with the new structure
        Schema::create('upload_fa_masters', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 100);
            $table->string('asset_name', 255);
            $table->string('location', 255);
            $table->string('cost_center', 100);
            $table->string('category', 100);
            $table->string('ledger', 100);
            $table->string('capitalize_date',100)->nullable();
            $table->integer('quantity')->default(1);
            $table->string( 'maintenance_schedule', 255)->nullable();
            $table->integer('useful_life')->nullable();
            $table->decimal('current_value', 18, 2)->default(0);
            $table->string('vendor', 255)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->string('book_date',100)->nullable();
            $table->string('import_status', 50)->nullable(); // NEW
            $table->string('import_remarks', 500)->nullable();   
            $table->bigInteger('created_by'); // NEW
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_fa_masters');
    }
}
