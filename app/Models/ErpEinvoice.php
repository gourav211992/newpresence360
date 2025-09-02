<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Traits\Deletable;

class ErpEinvoice extends Model
{
    use HasFactory,Deletable;
    protected $fillable = [
        'morphable_type',
        'morphable_id',
        'organization_id',
        'group_id',
        'company_id',
        'ack_no',
        'ack_date',
        'irn_number',
        'signed_invoice',
        'signed_qr_code',
        'ewb_no',
        'ewb_date',
        'ewb_valid_till',
        'status',
        'ewb_status',
        'Cancel_date',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $appends = ['display_irn'];

    public function getDisplayIrnAttribute()
    {
        $irnParts = [
            $this->ack_no,
            $this->ack_date,
            $this->irn_number
        ];
        return implode(', ', array_filter($irnParts));
    }

    public function erpMorphable()
    {
        return $this->morphTo();
    }

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

    public function source()
    {
        return $this->hasOne(ErpEinvoiceHistory::class, 'source_id');
    }

}
