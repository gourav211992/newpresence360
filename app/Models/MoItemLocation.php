<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoItemLocation extends Model
{
    use HasFactory;

    protected $table = 'erp_mo_item_locations';

    protected $fillable = [
        'mo_id',
        'mo_item_id',
        'item_id',
        'item_code',
        'store_id',
        'store_code',
        'rack_id',
        'rack_code',
        'shelf_id',
        'shelf_code',
        'bin_id',
        'bin_code',
        'quantity',
        'inventory_uom_qty'
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

    public function mo()
    {
        return $this->belongsTo(MfgOrder::class,'mo_id');
    }

    public function mo_item()
    {
        return $this->belongsTo(MoItem::class,'mo_item_id');
    }
    
    public function item()
    {
        return $this->belongsTo(Item::class,'item_id');
    }
}
