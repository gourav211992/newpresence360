<?php

namespace App\Models;

use App\Helpers\RGR\Constants as RgrConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FileUploadTrait;

class ErpRgrItemSegregation extends Model
{
    use HasFactory,FileUploadTrait;

    protected $appends = ['segregation_status'];

    protected $fillable = [
        'rgr_id',
        'rgr_item_id',
        'job_item_id',
        'item_id',
        'label_status',
        'delivery_cancel',
        'packing_status',
        'defect_severity',
        'defect_type',
        'damage_nature',
        'remarks',
        'new_item_id',
        'new_item_code',
        'new_item_name',
        'new_item_attributes',
    ];

    public function getSegregationStatusAttribute()
    {
        $statuses = [];
        //Take in account all details
        $packingStatus = $this -> packing_status;
        $defectSeverity = $this -> defect_severity;
        $defectType = $this -> defect_type;
        $damageNature = $this -> damage_nature;
        $isWrongProduct = isset($this -> new_item_id) ? true : false;
        if ($defectSeverity == RgrConstants::DAMAGE_NATURE_NO_DAMAGE) {
            $statuses[] = RgrConstants::RGR_SEGREGATION_OK_TO_RECIEVE;
        } else {
            $statuses[] = $damageNature;
        }
        //Packing Status
        if ($packingStatus == 0) {
            $statuses[] = RgrConstants::RGR_SEGREGATION_PACK_MISSING;
        }
        //Wrong Product 
        if ($isWrongProduct) {
            $statuses[] = RgrConstants::RGR_SEGREGATION_WRONG_PRODUCT;
        }
    }

     public function media()
    {
        return $this->morphMany(ErpRgrMedia::class, 'model');
    }
}