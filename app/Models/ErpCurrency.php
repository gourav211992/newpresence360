<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Deletable;

class ErpCurrency extends Model
{
    use HasFactory,Deletable;

    protected $table = "currency";
    protected $connection = "mysql_master";

    protected $fillable = [
        'name',
        'short_name',
        'symbol',
        'status',
    ];


     public function fromExchangeRates()
    {
        return $this->hasMany(ExchangeRate::class, 'from_currency_id');
    }

    public function toExchangeRates()
    {
        return $this->hasMany(ExchangeRate::class, 'upto_currency_id');
    }

}
