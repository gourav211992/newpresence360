<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Deletable;

class ErpShelf extends Model
{
    use HasFactory,Deletable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "erp_shelfs";
    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'erp_store_id',
        'erp_rack_id',
        'shelf_code',
        'shelf_name',
        'status'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function store() {
        return $this->belongsTo(ErpStore::class);
    }

    public function rack() {
        return $this->belongsTo(ErpRack::class);
    }
}
