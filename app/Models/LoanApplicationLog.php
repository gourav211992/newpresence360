<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanApplicationLog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'loan_application_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function logCreation($request, $homeLoan, $type, $user_id){
        $action_type = '';
        if($request->status_val == 1){
            $action_type = 'submitted';
        }else{
            $action_type = 'pending';
        }

        $logExists = self::checkActivityLog($homeLoan->id, $type, $user_id, $action_type);
        if(!$logExists){
            $loanApplicationLog = static::create([
                'loan_application_id' => $homeLoan->id,
                'loan_type' => $type,
                'action_type' => $action_type,
                'user_id' => $user_id,
                'remarks' => null
            ]);
        }
    }

    public static function checkActivityLog($id, $loan_type, $user_id, $action_type){
        $logExists = LoanApplicationLog::where('loan_application_id', $id)
                    ->where('loan_type', $loan_type)
                    ->where('user_id', $user_id)
                    ->where('action_type', $action_type)
                    ->exists();

        return $logExists;
    }
}
