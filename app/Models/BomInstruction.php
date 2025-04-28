<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FileUploadTrait;

class BomInstruction extends Model
{
    use HasFactory,FileUploadTrait;

    protected $table = 'erp_bom_instructions';
    protected $fillable = [
        'bom_id',
        'station_id',
        'section_id',
        'sub_section_id',
        'instructions'
    ];

    public function media()
    {
        return $this->morphMany(BomMedia::class, 'model');
    }
    
    public function bom()
    {
        return $this->belongsTo(Bom::class,'bom_id');
    }

    public function station()
    {
        return $this->belongsTo(Station::class,'station_id');
    }

    public function section()
    {
        return $this->belongsTo(ProductSection::class,'section_id');
    }

    public function subSection()
    {
        return $this->belongsTo(ProductSectionDetail::class,'sub_section_id');
    }

}
