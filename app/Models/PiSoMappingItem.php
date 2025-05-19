<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiSoMappingItem extends Model
{
    use HasFactory;

    protected $table = 'erp_pi_so_mapping_items';
    public $timestamps = false;
    protected $fillable = [
        'pi_so_mapping_id',
        'pi_item_id',
        'qty'
    ];

    public function pi_item()
    {
        return $this->belongsTo(PiItem::class,'pi_item_id');
    }

    public function pi_so_mapping()
    {
        return $this->belongsTo(PiSoMapping::class, 'pi_so_mapping_id', 'id');
    }
}
