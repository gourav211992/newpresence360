<?php
namespace App\Models\MaterialRequest;

use App\Models\User;
use App\Helpers\Helper;
use App\Models\Address;
use App\Models\Customer;
use App\Models\InvoiceBook;
use App\Models\Organization;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MrTedHistory extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;
    protected $table = 'erp_mr_ted_histories';

    protected $fillable = [
            'header_id',
            'header_history_id',
            'detail_id',
            'detail_history_id',
            'mr_ted_id',
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
            'deleted_at'
    ];

    public function MrHeader()
    {
        return $this->belongsTo(MrHeader::class);
    }

    public function MrDetail()
    {
        return $this->belongsTo(MrDetail::class);
    }

    public function MrHeaderHistory()
    {
        return $this->belongsTo(MrHeaderHistory::class);
    }

    public function MrDetailHistory()
    {
        return $this->belongsTo(MrDetailHistory::class);
    }

    public function MrExtraAmount()
    {
        return $this->belongsTo(MrTed::class);
    }
}



