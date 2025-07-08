<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceParameter extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql_master';

    protected $table = 'erp_service_parameters';

    public function __construct(array $attributes = [])
    {
        $this->table = config('database.connections.mysql_master.database') .'.'.$this->table;
        parent::__construct($attributes);
    }

    protected $fillable = [
        'service_id',
        'name',
        'applicable_values',
        'default_value',
        'type',
        'status'
    ];

    protected $casts = [
        'applicable_values' => 'array',
        'default_value' => 'array'
    ];

    public static function boot()
    {
        parent::boot();
        if(\Auth::check()) {

            static::creating(function ($model) {
                $user = Helper::getAuthenticatedUser();
                $model->created_by = $user->auth_user_id;
            });

            static::updating(function ($model) {
                $user = Helper::getAuthenticatedUser();
                $model->updated_by = $user->auth_user_id;
            });

            static::deleting(function ($model) {
                $user = Helper::getAuthenticatedUser();
                $model->deleted_by = $user->auth_user_id;
            });
        }   
    }
}
