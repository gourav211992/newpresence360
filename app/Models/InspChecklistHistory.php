<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InspChecklistHistory extends Model
{

    use HasFactory;

    protected $table = "erp_insp_checklists_history";
    protected $fillable = [
        'source_id',
        'header_id',
        'detail_id',
        'item_id',
        'checklist_id',
        'checklist_detail_id',
        'name',
        'value',
        'type',
    ];

    public function source()
    {
        return $this->belongsTo(InspChecklist::class, 'source_id');
    }

    public function header()
    {
        return $this->belongsTo(InspectionHeader::class, 'header_id');
    }

    public function detail()
    {
        return $this->belongsTo(InspectionDetail::class, 'detail_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function checklist()
    {
        return $this->belongsTo(InspectionChecklist::class, 'checklist_id');
    }

    public function checklistDetail()
    {
        return $this->belongsTo(InspectionChecklistDetail::class, 'checklist_detail_id');
    }

}