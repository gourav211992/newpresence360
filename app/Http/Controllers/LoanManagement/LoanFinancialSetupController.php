<?php

namespace App\Http\Controllers\LoanManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InterestRate;
use Carbon\Carbon;
use App\Models\Group;
use App\Models\Ledger;
use App\Helpers\Helper;
use App\Models\LoanFinancialAccount;

class LoanFinancialSetupController extends Controller
{

    public function index(Request $request)
    {
    
        $data = LoanFinancialAccount::first();

        return view('loan.financialsetup.view',compact('data')); // Adjust to your blade file path
    }



    public function add(Request $request)
    {
        $ledgers = Ledger::where('organization_id', Helper::getAuthenticatedUser()->organization_id)->select('id', 'name','ledger_group_id')->orderBy('id', 'desc')->get();
        $groups = Group::where('status', 'active')->where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNotNull('parent_group_id')->whereNull('organization_id');
            })->orWhere('organization_id', Helper::getAuthenticatedUser()->organization_id);
        })->select('id', 'name')->get();
       
        return view('loan.financialsetup.add',compact('ledgers','groups'));
    }

    public function create(Request $request)
    {
        $insert = new LoanFinancialAccount();
        $insert->pro_ledger_id = $request->pro_ledger_id;
        $insert->pro_ledger_group_id = $request->pro_ledger_group_id;
        $insert->dis_ledger_id = $request->dis_ledger_id;
        $insert->dis_ledger_group_id = $request->dis_ledger_group_id;
        $insert->int_ledger_id = $request->int_ledger_id;
        $insert->int_ledger_group_id = $request->int_ledger_group_id;
        $insert->wri_ledger_id = $request->wri_ledger_id;
        $insert->wri_ledger_group_id = $request->wri_ledger_group_id;
        $insert->status = $request->status;
        $insert->save();

        return redirect('/loan/financial-setup');
    }

    public function edit(Request $request)
    {
        $data = LoanFinancialAccount::find($request->id);

        $organizationId = Helper::getAuthenticatedUser()->organization_id;

        // Fetch ledgers
        $ledgers = Ledger::where('organization_id', $organizationId)
            ->select('id', 'name', 'ledger_group_id')
            ->orderBy('id', 'desc')
            ->get();

        // Common group query logic
        $groupsQuery = Group::where('status', 'active')->where(function ($q) use ($organizationId) {
            $q->where(function ($sub) {
                $sub->whereNotNull('parent_group_id')->whereNull('organization_id');
            })->orWhere('organization_id', $organizationId);
        });

        // Fetch groups
        $groups = $groupsQuery->select('id', 'name')->get();

       

        // Handle ledger IDs
        $ledgerid = $data->ledger_id ? explode(',', $data->ledger_id) : [];
        $groupledgerid = $data->ledger_group_id ? explode(',', $data->ledger_group_id) : [];



        return view('loan.financialsetup.edit',compact('ledgers','groups','data','ledgerid','groupledgerid'));
    }

    public function update(Request $request)
    {
        $insert = LoanFinancialAccount::find($request->id);
        $insert->pro_ledger_id = $request->pro_ledger_id;
        $insert->pro_ledger_group_id = $request->pro_ledger_group_id;
        $insert->dis_ledger_id = $request->dis_ledger_id;
        $insert->dis_ledger_group_id = $request->dis_ledger_group_id;
        $insert->int_ledger_id = $request->int_ledger_id;
        $insert->int_ledger_group_id = $request->int_ledger_group_id;
        $insert->wri_ledger_id = $request->wri_ledger_id;
        $insert->wri_ledger_group_id = $request->wri_ledger_group_id;
        $insert->status = $request->status;
        $insert->save();

        return redirect('/loan/financial-setup');
    }

    public function delete(Request $request)
    {
        $insert = LoanFinancialAccount::find($request->id);
        $insert->delete();

        return redirect('/loan/financial-setup');
    }
}
