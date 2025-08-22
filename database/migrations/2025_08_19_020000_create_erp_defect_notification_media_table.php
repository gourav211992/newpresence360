<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('erp_defect_notification_media', function (Blueprint $table) {
            $table->id();

            $table->morphs('model');
            $table->uuid()->nullable()->unique();
            $table->string('model_name');
            $table->string('collection_name');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('disk');
            $table->string('conversions_disk')->nullable();
            $table->unsignedBigInteger('size');
            $table->json('manipulations');
            $table->json('custom_properties');
            $table->json('generated_conversions');
            $table->json('responsive_images');
            $table->unsignedInteger('order_column')->nullable()->index();
            $table->nullableTimestamps();
        });

        // Remove attachment column from main table if it exists
        if (Schema::hasColumn('erp_defect_notifications', 'attachment')) {
            Schema::table('erp_defect_notifications', function (Blueprint $table) {
                $table->dropColumn('attachment');
            });
        }
        
        // Remove attachment column from history table if it exists
        if (Schema::hasColumn('erp_defect_notification_histories', 'attachment')) {
            Schema::table('erp_defect_notification_histories', function (Blueprint $table) {
                $table->dropColumn('attachment');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_defect_notification_media');
        
        // Restore attachment columns if needed
        if (!Schema::hasColumn('erp_defect_notifications', 'attachment')) {
            Schema::table('erp_defect_notifications', function (Blueprint $table) {
                $table->string('attachment')->nullable();
            });
        }
        
        if (!Schema::hasColumn('erp_defect_notification_histories', 'attachment')) {
            Schema::table('erp_defect_notification_histories', function (Blueprint $table) {
                $table->string('attachment')->nullable();
            });
        }
    }
};
