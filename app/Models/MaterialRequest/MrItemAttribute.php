<?php
namespace App\Models\MaterialRequest;

use App\Models\User;
use App\Helpers\Helper;
use App\Models\Address;
use App\Models\Customer;
use App\Models\InvoiceBook;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\Organization;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MrItemAttribute extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $table = 'erp_mr_item_attributes';

    protected $fillable = [
        'header_id',
        'detail_id',
        'item_id',
        'item_code',
        'item_attribute_id',
        'attr_name',
        'attr_value',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $hidden = ['deleted_at'];

    public function header()
    {
        return $this->belongsTo(MrHeader::class, 'header_id');
    }

    public function detail()
    {
        return $this->belongsTo(MrDetail::class, 'detail_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function itemAttribute()
    {
        return $this->belongsTo(ItemAttribute::class);
    }

}


