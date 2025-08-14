<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ApprovalWorkflow;
use App\Models\AssignTeam;
use Illuminate\Support\Facades\Auth;
use App\Traits\DefaultGroupCompanyOrg;
class Legal extends Model
{
    protected $table = 'erp_legals';

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->document_status = $model->status;
            $model->approval_level = $model->approvalLevel;
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    use HasFactory,DefaultGroupCompanyOrg;
    protected $guarded = ['id'];

    public function issues_detail()
    {
        return $this->belongsTo(IssueType::class, 'issues');
    }

    public function serie()
    {
        return $this->belongsTo(Book::class, 'series');
    }

    public function teams()
    {
        return $this->hasMany(AssignTeam::class, 'legalid', 'id');
    }

    public function emails()
    {
        return $this->hasMany(Email::class, 'legal_id', 'id');
    }


    public function approvals($series,$id)
    {

        if(!empty(Auth::guard('web')->user()))
        {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 'user';
            $utype = 1;

        }
        elseif (!empty(Auth::guard('web2')->user()))
        {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 'employee';
            $utype = 2;
        }
        else
        {
            $organization_id = 1;
            $user_id = 1;
            $type = 'user';
            $utype = 1;
        }

        $data = ApprovalWorkflow::where('book_id',$series)->first();

        $dl = Legal::where('id',$id)->where('user_id',$user_id)->where('type',$utype)->where('status','Approve')->first();

        if(!empty($dl))
        {
            return true;
        }

        if(!empty($data))
        {
            $daa = ApprovalWorkflow::where('book_id',$series)->where('user_id',$user_id)->where('user_type',$type)->first();
            if(!empty($daa))
            {
                return true;
            }
            else
            {
                return false;
            }

        }
        else
        {
            $dl = Legal::where('id',$id)->where('user_id',$user_id)->where('type',$utype)->first();

            if(!empty($dl))
            {
                return true;
            }
            else
            {
                if($utype == 2)
                {
                    $as = AssignTeam::where('legalid',$id)->where('team',$user_id)->first();

                    if(!empty($as))
                    {
                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }

                return false;
            }

        }
    }

    public function approvalsworkflows($series,$id)
    {

        if(!empty(Auth::guard('web')->user()))
        {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 'user';
            $utype = 1;

        }
        elseif (!empty(Auth::guard('web2')->user()))
        {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 'employee';
            $utype = 2;
        }
        else
        {
            $organization_id = 1;
            $user_id = 1;
            $type = 'user';
            $utype = 1;
        }

        $data = ApprovalWorkflow::where('book_id',$series)->first();

        if(!empty($data))
        {
            $daa = ApprovalWorkflow::where('book_id',$series)->where('user_id',$user_id)->where('user_type',$type)->first();
            if(!empty($daa))
            {
                return true;
            }
            else
            {
                return false;
            }

        }
        else
        {
             return false;
        }
    }

   public function approvelworkflow()
{
    return $this->hasMany(ApprovalWorkflow::class, 'book_id', 'series');
}





}
