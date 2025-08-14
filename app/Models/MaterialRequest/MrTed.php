<?php
namespace App\Models\MaterialRequest;

use App\Models\User;
use App\Helpers\Helper;
use App\Models\Address;
use App\Models\Customer;
use App\Models\InvoiceBook;
use App\Models\Organization;
use App\Models\TaxDetail;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MrTed extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;
    protected $table = 'erp_mr_ted';

    protected $fillable = [
        'header_id',
        'detail_id',
        'ted_id',
        'ted_type',
        'ted_level',
        'book_code',
        'document_number',
        'ted_name',
        'ted_code',
        'assesment_amount',
        'ted_percentage',
        'ted_amount',
        'applicability_type',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function header()
    {
        return $this->belongsTo(MrHeader::class, 'header_id');
    }

    public function detail()
    {
        return $this->belongsTo(MrDetail::class, 'detail_id');
    }

    public function taxDetail()
    {
        return $this->belongsTo(TaxDetail::class, 'ted_id');
    }
}





