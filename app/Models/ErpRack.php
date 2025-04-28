<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Deletable;

class ErpRack extends Model
{
    use HasFactory,Deletable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'erp_store_id',
        'rack_code',
        'rack_name',
        'status'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function store() {
        return $this->belongsTo(ErpStore::class);
    }

    public function shelfs() {
        return $this->hasMany(ErpShelf::class);
    }
    public function rack_shelfs() {
        return $this->hasMany(ErpShelf::class)->select('id', 'erp_rack_id', 'shelf_code');
    }
}
