<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
class ErpCurrencyExchange extends Model
{
    use HasFactory,Deletable,DefaultGroupCompanyOrg;

    protected $table = 'erp_currency_exchanges';

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'from_currency_id',
        'upto_currency_id',
        'from_date',
        'exchange_rate',
        'status',
        'created_by',
    ];

    public function fromCurrency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function uptoCurrency()
    {
        return $this->belongsTo(Currency::class);
    }
}
