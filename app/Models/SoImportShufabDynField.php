<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoImportShufabDynField extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "erp_so_import_shufab_dyn_fields";

    protected $fillable = [
        'import_id',
        'dyn_header_id',
        'dyn_detail_id',
        'name',
        'value'
    ];
}
