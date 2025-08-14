<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\Helper;

class LoanReturn extends Model
{
    protected static function boot()
    {
        $user = Helper::getAuthenticatedUser();
    
        parent::boot();
        static::saving(function ($model) use ($user) {
            if (is_null($model->on_behalf)) {
                $model->on_behalf = $user->auth_user_id;
                $model->on_behalf_type = $user->authenticable_type; 
            }
        });
    }
    protected $table = 'erp_loan_return';
    protected $fillable = ['loan_application_id', 'status','return_page_status', 'doc', 'on_behalf','on_behalf_type','remarks'];

    use HasFactory;
    use SoftDeletes;
}
