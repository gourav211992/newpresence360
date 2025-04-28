<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiSoMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'so_id',
        'so_item_id',
        'item_id',
        'created_by',
        'bom_id',
        'bom_detail_id',
        'item_code',
        'order_qty',
        'bom_qty',
        'qty',
        'pi_item_qty',
        'attributes',
        'child_bom_id'
    ];

    protected $table = 'erp_pi_so_mapping';

    protected $appends = [
        'bom_item_qty'
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

    public function item()
    {
        return $this->belongsTo(Item::class,'item_id');
    }

    public function soItem()
    {
        return $this->belongsTo(ErpSoItem::class,'so_item_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class,'so_id');
    }

    public function bomDetail()
    {
        return $this->belongsTo(BomDetail::class,'bom_detail_id');
    }

    public function soAttributes()
    {
        return $this->hasMany(ErpSoItemAttribute::class,'so_item_id','so_item_id');
    }

    public function getBomItemQtyAttribute()
    {
        $qty = 0;
        if($this?->bomDetail) {
            $qty = floatval($this->bomDetail->qty);
            return $qty;  
        }
        return $qty;
    }

    public function pi_so_mapping_item()
    {
        return $this->hasOne(PiSoMappingItem::class,'pi_so_mapping_id');
    }
}
