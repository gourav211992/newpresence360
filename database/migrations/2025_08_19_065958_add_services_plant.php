<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('erp_services')->insert([
            [
                'name' => 'Equipment',
                'alias' => 'equipment',
                'type' => 'transaction',
                'icon' => null,
                'financial_service_alias' => null,
                'status' => 'active',
                'created_by' => null,
                'updated_by' => null,
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Maintenance BOM',
                'alias' => 'maint-bom',
                'type' => 'transaction',
                'icon' => null,
                'financial_service_alias' => null,
                'status' => 'active',
                'created_by' => null,
                'updated_by' => null,
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Defect Notification',
                'alias' => 'defect-notification',
                'type' => 'transaction',
                'icon' => null,
                'financial_service_alias' => null,
                'status' => 'active',
                'created_by' => null,
                'updated_by' => null,
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Maintenance WO',
                'alias' => 'maint-wo',
                'type' => 'transaction',
                'icon' => null,
                'financial_service_alias' => null,
                'status' => 'active',
                'created_by' => null,
                'updated_by' => null,
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('erp_services')->whereIn('alias', ['maint-wo', 'maint-bom', 'equipment','defect-notification'])->delete();
    }
};
