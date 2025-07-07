<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;

class Currency extends Model
{
    use HasFactory,Deletable;

    protected $connection = 'mysql_master';
    protected $table = "currency";

    protected $fillable = [
        'name',
        'short_name',
        'symbol',
        'status',
    ];


     public function fromExchangeRates()
    {
        return $this->hasMany(CurrencyExchange::class, 'from_currency_id');
    }

    public function toExchangeRates()
    {
        return $this->hasMany(CurrencyExchange::class, 'upto_currency_id');
    }

}
