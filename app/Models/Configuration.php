<?php
namespace App\Models;

use App\Helpers\Helper;
use App\Helpers\ServiceParametersHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{

    protected $table = 'configurations';

    use HasFactory;
    public $log_module_name = 'Master';
    public $log_source = null;

    protected $fillable = [
        'type',
        'type_id',
        'config_key',
        'config_value',
        'created_by',
        'updated_by'
    ];

    protected $hidden = [
        'deleted_at'
    ];

    public function updateorSaveData($obj, $user)
    {
        if ($obj->config_key) {
            foreach ($obj->config_key as $key => $configKey) {
                $configData = Configuration::where('type', '=', $obj->type)
                    ->where('type_id', $obj->type_id)
                    ->where('config_key', '=', $configKey)
                    ->first();
                if ($configData) {
                    $configData->config_value = $obj->config_value[$key];
                    $configData->updated_by = $user->id;
                    $configData->save();
                } else {
                    $config = new Configuration();
                    $config->type = $obj->type;
                    $config->type_id = $obj->type_id;
                    $config->config_key = $configKey;
                    $config->config_value = $obj->config_value[$key];
                    $config->created_by = $user->id;
                    $config->save();
                }
            }
        }
        return;
    }
}
