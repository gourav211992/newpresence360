<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Deletable;

class ErpBin extends Model
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
        'erp_rack_id',
        'erp_shelf_id',
        'bin_code',
        'bin_name',
        'status'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function erpStore()
    {
        return $this->belongsTo(ErpStore::class);
    }

    public function erpRack()
    {
        return $this->belongsTo(ErpRack::class);
    }

    public function erpShelf()
    {
        return $this->belongsTo(ErpShelf::class);
    }
}
