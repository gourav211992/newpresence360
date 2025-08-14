<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class EwayBillMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql_master';


    protected $fillable = [
        'type', 
        'code', 
        'description', 
        'status', 
        'created_by', 
        'updated_by', 
        'deleted_by'
    ];

    protected $auditInclude = [
        'type', 
        'code', 
        'description',
        'status',
    ];

    public static function boot()
    {
        parent::boot();
        if (auth()->check()) {
            static::creating(function ($model) {
                $model->created_by = auth()->user()->id;
            });

            static::updating(function ($model) {
                $model->updated_by = auth()->user()->id;
            });

            static::deleting(function ($model) {
                $model->deleted_by = auth()->user()->id;
            });
        }
    }
}
