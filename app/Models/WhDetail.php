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

class WhDetail extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait, FileUploadTrait, Deletable;
    protected $table = 'erp_wh_details';
    protected $fillable = [
        'name',
        'heirarchy_name',
        'wh_level_id',
        'store_id',
        'sub_store_id',
        'parent_id',
        'is_storage_point',
        'is_first_level',
        'is_last_level',
        'max_weight',
        'max_volume',
        'current_weight',
        'current_volume',
        'storage_number',
        'status',
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

    public function whLevel()
    {
        return $this->belongsTo(WhLevel::class, 'wh_level_id');
    }

    public function store()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    public function sub_store()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }

    public function parent()
    {
        return $this->belongsTo(WhDetail::class, 'parent_id');
    }

    public function getParentNamesAttribute()
    {
        $colors = [
            'badge-light-primary',
            'badge-light-success',
            'badge-light-warning',
            'badge-light-danger',
            'badge-light-info',
            'badge-light-dark',
        ];

        $badges = '';
        $level = $this->parent;
        $index = 0;

        while ($level) {
            $colorClass = $colors[$index % count($colors)]; // Cycle through colors
            $badges .= '<span class="badge rounded-pill ' . $colorClass . ' badgeborder-radius" style="margin-right: 5px;">'
                . $level->name .
                '</span>';

            $level = $level->parent;
            $index++;
        }

        return $badges;
    }

    public function getParentNames()
    {
        $parents = [];
        $level = $this->parent;

        while ($level) {
            $parents[] = [
                'id' => $level->id,
                'name' => $level->name,
            ];
            $level = $level->parent;
        }

        return $parents;
    }

    public function getLevelNamesAttribute()
    {
        $colors = [
            'badge-light-primary',
            'badge-light-success',
            'badge-light-warning',
            'badge-light-danger',
            'badge-light-info',
            'badge-light-dark',
        ];

        $index = 0;
        $badges = '';

        // Assuming you have a sub_store_id field available in $this
        $levels = WhDetail::where('sub_store_id', $this->sub_store_id)
            ->where('wh_level_id', $this->wh_level_id)
            ->groupBy('name')
            ->get();

        foreach ($levels as $level) {
            $colorClass = $colors[$index % count($colors)]; // Cycle through colors
            $badges .= '<span class="badge rounded-pill ' . $colorClass . ' badgeborder-radius" style="margin-right: 5px;">'
                . $level->name .
                '</span>';
            $index++;
        }

        return $badges;
    }
}
