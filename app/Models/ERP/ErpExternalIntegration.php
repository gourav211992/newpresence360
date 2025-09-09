<?php
namespace App\Models\ERP;

use App\Models\Book;
use App\Models\ErpCustomer;
use App\Models\ErpStore;
use App\Models\Organization;
use App\Models\OrganizationCompany;
use App\Models\OrganizationGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpExternalIntegration extends Model
{
    use SoftDeletes;


     /**
     * The table associated with the model.
     */
    protected $table = 'erp_external_integrations';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'book_id',
        'store_id',
        'customer_id',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $hidden = ['deleted_at', 'customer_id'];

    public function customer()
    {
        return $this->belongsTo(ErpCustomer::class, 'group_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function store()
    {
        return $this->belongsTo(ErpStore::class);
    }


    public function group()
    {
        return $this->belongsTo(OrganizationGroup::class, 'group_id');
    }

    public function company()
    {
        return $this->belongsTo(OrganizationCompany::class, 'company_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }
}

?>
