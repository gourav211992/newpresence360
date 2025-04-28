<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomUpload extends Model
{
    use HasFactory;
    protected $table = 'erp_bom_uploads';
    public $timestamps = true;
    protected $casts = ['product_attributes' => 'array', 'item_attributes' => 'array', 'reason' => 'array'];
    protected $fillable = [
        'type',
        'customizable',
        'bom_type',
        'production_route_id',
        'production_route_name',
        'uom_id',
        'uom_code',
        'production_type',
        'product_item_id',
        'product_item_code',
        'product_item_name',
        'product_attributes',
        'item_id',
        'item_code',
        'item_uom_id',
        'item_uom_code',
        'item_attributes',
        'item_attributes',
        'attribute_name_1',
        'attribute_value_1',
        'attribute_name_2',
        'attribute_value_2',
        'attribute_name_3',
        'attribute_value_3',
        'attribute_name_4',
        'attribute_value_4',
        'attribute_name_5',
        'attribute_value_5',
        'consumption_qty',
        'cost_per_unit',
        'station_id',
        'station_name',
        'migrate_status',
        'bom_id',
        'reason',
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by'
    ];
    
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->created_by = $user->auth_user_id;
            }
        });
        static::updating(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->updated_by = $user->auth_user_id;
            }
        });
        static::deleting(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->deleted_by = $user->auth_user_id;
            }
        });
    }
}
