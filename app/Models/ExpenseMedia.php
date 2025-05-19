<?php
namespace App\Models;

use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExpenseMedia extends Model
{
    use HasFactory,FileUploadTrait;

    protected $table = 'erp_expense_media';

    protected $fillable = [
        'uuid',
        'model_name',
        'model_type',
        'collection_name',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'size',
        'model_id',
        'manipulations', // Add any other fields that should be mass assignable
        'custom_properties',
        'generated_conversions',
        'responsive_images',
        'order_column',
    ];
}
