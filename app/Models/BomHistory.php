<?php

namespace App\Models;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Traits\DateFormatTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomHistory extends Model
{
    use HasFactory,DateFormatTrait,FileUploadTrait;

    protected $table = 'erp_boms_history';

    protected $fillable = [
        'organization_id',
        'source_id',
        'group_id',
        'company_id',
        'uom_id',
        'production_type',
        'item_id',
        'book_id',
        'book_code',
        'document_number',
        'document_date',
        'document_status',
        'revision_number',
        'revision_date',
        'item_code',
        'item_name',
        'qty_produced',
        'total_item_value',
        'item_waste_amount',
        'item_overhead_amount',
        'header_waste_perc',
        'header_waste_amount',
        'header_overhead_amount',
        'remarks',
        'status',
        'approval_level',
        'type',
        'customer_id',
        'bom_type',
        'customizable',
        'safety_buffer_perc'
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

    public $referencingRelationships = [
        'item' => 'item_id',
        'uom' => 'uom_id',
        'book' => 'book_id'
    ];

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
    
    public function customer()
    {
        return $this->belongsTo(ErpCustomer::class, 'customer_id');
    }
    
    public function media()
    {
        return $this->morphMany(BomMedia::class, 'model');
    }
    
    public function getDisplayStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->document_status);
        return ucwords($status);
    }
    
    public function setAttachmentAttribute($value)
    {
        $this->attributes['attachment'] = empty($value) ? '{}' : json_encode($value);
    }
    
    public function getAttachmentAttribute()
    {
        $media = $this->getMedia('attachment')->first();
        return $media ? $media->getFullUrl() : '';
    }

    public function getTotalValueAttribute()
    {
        $t = $this->total_item_value + $this->item_overhead_amount + $this->header_overhead_amount;
        return $t;
    }

    public function getDocumentStatusAttribute()
    {
        if ($this->attributes['document_status'] == ConstantHelper::APPROVAL_NOT_REQUIRED) {
            return ConstantHelper::APPROVED;
        }
        return $this->attributes['document_status'];
    }


    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function bomItems()
    {
        return $this->hasMany(BomDetailHistory::class, 'bom_id');
    }   

    public function bomOverheadAllItems()
    {
        return $this->hasMany(BomOverheadHistory::class, 'bom_id');
    }

    /*For this header overhead*/
    public function bomOverheadItems()
    {
        return $this->hasMany(BomOverheadHistory::class, 'bom_id')->where('type','H');
    }

    /*For this component overhead*/
    public function bomComponentOverheadItems()
    {
        return $this->hasMany(BomOverheadHistory::class, 'bom_id')->where('type','D');
    }

    public function bomAllAttributes()
    {
        return $this->hasMany(BomAttributeHistory::class, 'bom_id');
    }

    # get header level
    public function bomAttributes()
    {
        return $this->hasMany(BomAttributeHistory::class, 'bom_id')->where('type','H');
    }

    # get item level
    public function bomItemAttributes()
    {
        return $this->hasMany(BomAttributeHistory::class, 'bom_id')->where('type','D');
    }

}
