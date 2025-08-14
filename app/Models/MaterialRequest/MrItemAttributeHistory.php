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

class MrItemAttributeHistory extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;
    protected $table = 'erp_mr_item_attribute_histories';

    protected $fillable = [
        'id',
        'header_id',
        'header_history_id',
        'detail_id',
        'detail_history_id',
        'item_id',
        'item_code',
        'attribute_id',
        'item_attribute_id',
        'attr_name',
        'attr_value',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $hidden = ['deleted_at'];

    public function MrHeader()
    {
        return $this->belongsTo(MrHeader::class);
    }

    public function MrDetail()
    {
        return $this->belongsTo(MrDetail::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function itemAttribute()
    {
        return $this->belongsTo(ItemAttribute::class);
    }

    public function MrAttribute()
    {
        return $this->belongsTo(MrItemAttribute::class);
    }

    public function MrHeaderHistory()
    {
        return $this->belongsTo(MrHeaderHistory::class);
    }

    public function MrDetailHistory()
    {
        return $this->belongsTo(MrDetailHistory::class);
    }
}
