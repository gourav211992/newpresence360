<?php
namespace App\Models;

use App\Models\User;
use App\Helpers\Helper;
use App\Models\Organization;
use App\Helpers\ConstantHelper;
use App\Traits\DateFormatTrait;
use App\Traits\FileUploadTrait;
use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhItemMapping extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait, FileUploadTrait, Deletable;
    protected $table = 'erp_wh_item_mappings';
    protected $fillable = [
        'wh_structure_id',
        'store_id',
        'sub_store_id',
        'category_id',
        'sub_category_id',
        'item_id',
        'wh_level_id',
        'wh_detail_id',
        'structure_details', // ← Add this
        'status',
    ];

    protected $casts = [
        'category_id' => 'array',
        'sub_category_id' => 'array',
        'item_id' => 'array',
        'structure_details' => 'array', // ← Optional but helpful
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

    public function whStructure()
    {
        return $this->belongsTo(WhStructure::class, 'wh_structure_id');
    }

    public function whLevel()
    {
        return $this->belongsTo(WhLevel::class, 'wh_level_id');
    }

    public function whDetail()
    {
        return $this->belongsTo(WhDetail::class, 'wh_detail_id');
    }

    public function store()
    {
        return $this -> belongsTo(ErpStore::class, 'store_id');
    }

    public function sub_store()
    {
        return $this -> belongsTo(ErpSubStore::class, 'sub_store_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subCategory()
    {
        return $this -> belongsTo(Category::class, 'sub_category_id');
    }

    public function item()
    {
        return $this -> belongsTo(ErpItem::class, 'item_id');
    }

}
