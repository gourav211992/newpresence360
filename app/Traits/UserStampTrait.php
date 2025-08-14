<?php

namespace App\Traits;
use App\Helpers\Helper;

trait UserStampTrait
{
    /* Use carefully -> Only use for model containing following columns ->
        created_by INT
        updated_by INT
        deleted_by INT 
    */

    public static function boot()
    {
        parent::boot();
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
