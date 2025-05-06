<?php

namespace App\Http\Controllers;

use App\Exports\PLLevel1ReportExport;
use App\Exports\PLLevel2ReportExport;
use App\Helpers\Helper;
use App\Models\Group;
use App\Models\ItemDetail;
use App\Models\Ledger;
use App\Models\Organization;
use App\Models\PLGroups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\CostCenterOrgLocations;

class ProfitLossController extends Controller
{
    public function exportPLLevel(Request $r){
        $r = (object) array_merge($r['obj'], [
            'type' => $r['type'] ?? null,
            'auth_type' => $r['auth_type'] ?? null,
        ]);
        $dateRange = $r->date;
        $currency="org";
        if ($r->currency!="") {
            $currency = $r->currency;
        };

        if ($r->date=="") {
            $financialYear = Helper::getFinancialYear(date('Y-m-d'));
            $startDate=$financialYear['start_date'];
            $endDate=$financialYear['end_date'];
            $dateRange = $startDate.' to '.$endDate;
        }
        else {
            $dates = explode(' to ', $r->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
        }
        $organizations=[];
        if ($r->organization_id && is_array($r->organization_id)) {
            $organizations = $r->organization_id;
        };
        if(count($organizations) == 0){
            $organizations[]=Helper::getAuthenticatedUser()->organization_id;
        }
        $organizationName=implode(",",DB::table('organizations')->whereIn('id',$organizations)->pluck('name')->toArray());
        $plData=Helper::getPlGroupsData($startDate,$endDate,$organizations,$currency,$r->cost_center_id,"profitloss");

        $sales=0;
        $purchase=0;
        $saleInd=0;
        $purchaseInd=0;

        $indExpTotal=0;
        $indIncTotal=0;

        $data = [];

        $openingAmount=0;
        $purchaseAmount=0;
        $directExpenseAmount=0;
        $indirectExpenseAmount=0;
        $saleAmount=0;
        $directIncomeAmount=0;
        $closingAmount=0;
        $indirectIncomeAmount=0;

        $openingLedgers=[];
        $purchaseLedgers=[];
        $directExpenseLedgers=[];
        $indirectExpenseLedgers=[];
        $saleLedgers=[];
        $directIncomeLedgers=[];
        $closingLedgers=[];
        $indirectIncomeLedgers=[];
        foreach ($plData as $pl) {
            if($pl->name == "Opening Stock"){
                $purchase=$pl->closing + $purchase;
                $openingAmount=abs($pl->closing);
                if ($r->level==2) {
                    $openingLedgers=$this->getGroupLedgers($pl->id,$organizations,$startDate,$endDate,$currency,$r->cost_center_id);
                }
            }
            if($pl->name == "Purchase Accounts"){
                $purchase=$pl->closing + $purchase;
                $purchaseAmount=abs($pl->closing);
                if ($r->level==2) {
                    $purchaseLedgers=$this->getGroupLedgers($pl->id,$organizations,$startDate,$endDate,$currency,$r->cost_center_id);
                }
            }
            if($pl->name == "Direct Expenses"){
                $purchase=$pl->closing + $purchase;
                $directExpenseAmount=abs($pl->closing);
                if ($r->level==2) {
                    $directExpenseLedgers=$this->getGroupLedgers($pl->id,$organizations,$startDate,$endDate,$currency,$r->cost_center_id);
                }
            }
            if($pl->name == "Indirect Expenses"){
                $purchaseInd=$pl->closing + $purchaseInd;
                $indExpTotal=$pl->closing;
                $indirectExpenseAmount=abs($pl->closing);
                if ($r->level==2) {
                    $indirectExpenseLedgers=$this->getGroupLedgers($pl->id,$organizations,$startDate,$endDate,$currency,$r->cost_center_id);
                }
            }
            if($pl->name == "Sales Accounts"){
                $sales=$pl->closing + $sales;
                $saleAmount=abs($pl->closing);
                if ($r->level==2) {
                    $saleLedgers=$this->getGroupLedgers($pl->id,$organizations,$startDate,$endDate,$currency,$r->cost_center_id);
                }
            }
            if($pl->name == "Direct Income"){
                $sales=$pl->closing + $sales;
                $directIncomeAmount=abs($pl->closing);
                if ($r->level==2) {
                    $directIncomeLedgers=$this->getGroupLedgers($pl->id,$organizations,$startDate,$endDate,$currency,$r->cost_center_id);
                }
            }
            if($pl->name == "Indirect Income"){
                $saleInd=$pl->closing + $saleInd;
                $indIncTotal=$pl->closing;
                $indirectIncomeAmount=abs($pl->closing);
                if ($r->level==2) {
                    $indirectIncomeLedgers=$this->getGroupLedgers($pl->id,$organizations,$startDate,$endDate,$currency,$r->cost_center_id);
                }
            }
        }

        $difference=$sales - $purchase;
        $diffVal=$difference < 0 ? -$difference : $difference;

        $grossProfit=0;
        $grossLoss=0;
        $subTotal=0;
        if ($sales > $purchase) {
            $grossProfit=$diffVal;
            $grossLoss=0;
            $subTotal=$sales;
            $saleInd=$diffVal + $saleInd;
        } else {
            $grossLoss=$diffVal;
            $grossProfit=0;
            $subTotal=$purchase;
            $purchaseInd=$diffVal + $purchaseInd;
        }

        $overAllTotal=0;
        $netProfit=0;
        $netLoss=0;
        if ($saleInd > $purchaseInd) {
            $overAllTotal=$saleInd;
            $netProfit=$saleInd - $indExpTotal;
            $netLoss=0;
        } else{
            $overAllTotal=$purchaseInd;
            $netLoss=$purchaseInd - $indIncTotal;
            $netProfit=0;
        }

        if ($r->level==1) {
            $data[] = ['Opening Stock', Helper::formatIndianNumber($openingAmount),'','Sales Accounts', Helper::formatIndianNumber($saleAmount)];
            $data[] = ['Purchase Accounts', Helper::formatIndianNumber($purchaseAmount),'','Direct Income', Helper::formatIndianNumber($directIncomeAmount)];
            $data[] = ['Direct Expenses', Helper::formatIndianNumber($directExpenseAmount),'','Closing Stock', Helper::formatIndianNumber($closingAmount)];
            $data[] = ['Gross Profit c/o', Helper::formatIndianNumber($grossProfit),'','Gross Loss c/o', Helper::formatIndianNumber($grossLoss)];
            $data[] = ['', Helper::formatIndianNumber($subTotal),'','', Helper::formatIndianNumber($subTotal)];
            $data[] = ['Gross Loss b/f', Helper::formatIndianNumber($grossLoss),'','Gross Profit b/f', Helper::formatIndianNumber($grossProfit)];
            $data[] = ['Indirect Expenses', Helper::formatIndianNumber($indirectExpenseAmount),'','Indirect Income', Helper::formatIndianNumber($indirectIncomeAmount)];
            $data[] = ['Net Profit', Helper::formatIndianNumber($netProfit),'','Net Loss', Helper::formatIndianNumber($netLoss)];
            $data[] = ['Total', Helper::formatIndianNumber($overAllTotal),'','Total', Helper::formatIndianNumber($overAllTotal)];
            if($r->type=="pdf")
            return Excel::download(new PLLevel1ReportExport($organizationName, $dateRange, $data), 'plLevel1Report.pdf',\Maatwebsite\Excel\Excel::DOMPDF);
            else
            return Excel::download(new PLLevel1ReportExport($organizationName, $dateRange, $data), 'plLevel1Report.xlsx');
          
          
        } else {
            $data[] = ['Opening Stock','', Helper::formatIndianNumber($openingAmount),'','Sales Accounts','', Helper::formatIndianNumber($saleAmount)];
            $loopLength=Helper::checkCount($openingLedgers)>Helper::checkCount($saleLedgers) ? Helper::checkCount($openingLedgers) : Helper::checkCount($saleLedgers);
            for ($i=0; $i <$loopLength ; $i++) {
                $secName1=$openingLedgers->get($i)->name ?? '';
                $secAmount1=$openingLedgers->get($i)->closing ?? 0;
                $secName2=$saleLedgers->get($i)->name ?? '';
                $secAmount2=$saleLedgers->get($i)->closing ?? 0;
                $data[] = ['',$secName1, Helper::formatIndianNumber($secAmount1),'','',$secName2, Helper::formatIndianNumber($secAmount2)];
            }
            $data[] = ['Purchase Accounts','', Helper::formatIndianNumber($purchaseAmount),'','Direct Income','', Helper::formatIndianNumber($directIncomeAmount)];
            $loopLength=Helper::checkCount($purchaseLedgers)>Helper::checkCount($directIncomeLedgers) ? Helper::checkCount($purchaseLedgers) : Helper::checkCount($directIncomeLedgers);
            for ($i=0; $i <$loopLength ; $i++) {
                $secName1=$purchaseLedgers->get($i)->name ?? '';
                $secAmount1=$purchaseLedgers->get($i)->closing ?? 0;
                $secName2=$directIncomeLedgers->get($i)->name ?? '';
                $secAmount2=$directIncomeLedgers->get($i)->closing ?? 0;
                $data[] = ['',$secName1, Helper::formatIndianNumber($secAmount1),'','',$secName2, Helper::formatIndianNumber($secAmount2)];
            }
            $data[] = ['Direct Expenses','', Helper::formatIndianNumber($directExpenseAmount),'','Closing Stock','', Helper::formatIndianNumber($closingAmount)];
            $loopLength=Helper::checkCount($directExpenseLedgers)>Helper::checkCount($closingLedgers) ? Helper::checkCount($directExpenseLedgers) : Helper::checkCount($closingLedgers);
            for ($i=0; $i <$loopLength ; $i++) {
                $secName1=$directExpenseLedgers->get($i)->name ?? '';
                $secAmount1=$directExpenseLedgers->get($i)->closing ?? 0;
                $secName2='';
                $secAmount2=0;
                $data[] = ['',$secName1, Helper::formatIndianNumber($secAmount1),'','',$secName2, Helper::formatIndianNumber($secAmount2)];
            }
            $data[] = ['Gross Profit c/o','', Helper::formatIndianNumber($grossProfit),'','Gross Loss c/o','', Helper::formatIndianNumber($grossLoss)];
            $data[] = ['','', Helper::formatIndianNumber($subTotal),'','','', Helper::formatIndianNumber($subTotal)];
            $data[] = ['Gross Loss b/f','', Helper::formatIndianNumber($grossLoss),'','Gross Profit b/f','', Helper::formatIndianNumber($grossProfit)];
            $data[] = ['Indirect Expenses','', Helper::formatIndianNumber($indirectExpenseAmount),'','Indirect Income','', Helper::formatIndianNumber($indirectIncomeAmount)];
            $loopLength=Helper::checkCount($indirectExpenseLedgers)>Helper::checkCount($indirectIncomeLedgers) ? Helper::checkCount($indirectExpenseLedgers) : Helper::checkCount($indirectIncomeLedgers);
            for ($i=0; $i <$loopLength ; $i++) {
                $secName1=$indirectExpenseLedgers->get($i)->name ?? '';
                $secAmount1=$indirectExpenseLedgers->get($i)->details_sum_debit_amt ?? 0;
                $secName2=$indirectIncomeLedgers->get($i)->name ?? 0;
                $secAmount2=$indirectIncomeLedgers->get($i)->details_sum_debit_amt ?? 0;
                $data[] = ['',$secName1, Helper::formatIndianNumber($secAmount1),'','',$secName2, Helper::formatIndianNumber($secAmount2)];
            }
            $data[] = ['Net Profit','', Helper::formatIndianNumber($netProfit),'','Net Loss','', Helper::formatIndianNumber($netLoss)];
            $data[] = ['Total','', Helper::formatIndianNumber($overAllTotal),'','Total','', Helper::formatIndianNumber($overAllTotal)];
            if($r->type=="pdf")
            return Excel::download(new PLLevel2ReportExport($organizationName, $dateRange, $data), 'plLevel2Report.pdf',\Maatwebsite\Excel\Excel::DOMPDF,);
            else
            return Excel::download(new PLLevel2ReportExport($organizationName, $dateRange, $data), 'plLevel2Report.xlsx');
        
        }
    }

    public function plGroup(){
        $groups = Group::where('status','active')->where(function($q){
            $q->where(function($sub){
                $sub->whereNotNull('parent_group_id')->whereNull('organization_id');
            })->orWhere('organization_id',Helper::getAuthenticatedUser()->organization_id);
        })->select('id','name')->get();

        $plGroups=PLGroups::get();
        return view('profitLoss.groups',compact('groups','plGroups'));
    }

    public function plGroupStore(Request $request)
    {
        $request->validate([
            'openingStock' => 'required|array',
            'purchaseAccounts' => 'required|array',
            'directExpenses' => 'required|array',
            'indirectExpenses' => 'required|array',
            'salesAccounts' => 'required|array',
            'directIncome' => 'required|array',
            'indirectIncome' => 'required|array',
        ]);

        // $insert = new PLGroups();
        // $insert->name = $request->name;
        // $insert->group_ids = $request->group_id;
        // $insert->save();

        PLGroups::updateOrCreate(
            ['name' => 'Opening Stock'],
            ['name' => 'Opening Stock', 'group_ids' => $request->openingStock]
        );
        PLGroups::updateOrCreate(
            ['name' => 'Purchase Accounts'],
            ['name' => 'Purchase Accounts', 'group_ids' => $request->purchaseAccounts]
        );
        PLGroups::updateOrCreate(
            ['name' => 'Direct Expenses'],
            ['name' => 'Direct Expenses', 'group_ids' => $request->directExpenses]
        );
        PLGroups::updateOrCreate(
            ['name' => 'Indirect Expenses'],
            ['name' => 'Indirect Expenses', 'group_ids' => $request->indirectExpenses]
        );
        PLGroups::updateOrCreate(
            ['name' => 'Sales Accounts'],
            ['name' => 'Sales Accounts', 'group_ids' => $request->salesAccounts]
        );
        PLGroups::updateOrCreate(
            ['name' => 'Direct Income'],
            ['name' => 'Direct Income', 'group_ids' => $request->directIncome]
        );
        PLGroups::updateOrCreate(
            ['name' => 'Indirect Income'],
            ['name' => 'Indirect Income', 'group_ids' => $request->indirectIncome]
        );

        return redirect()->route("finance.plGroup")->with('success', 'Groups created successfully');
    }

    public function profitLoss(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organizationId = Helper::getAuthenticatedUser()->organization_id;
        $companies = $user -> access_rights_org;
        $organizations = Helper::getAuthenticatedUser()->access_rights_org;
        $organization=Organization::where('id',Helper::getAuthenticatedUser()->organization_id)->value('name');
        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
        }
        else{
            $fyear = Helper::getFinancialYear(date('Y-m-d'));
                $startDate = $fyear['start_date'];
                $endDate = $fyear['end_date'];
            
        
        }
        $cost_centers = CostCenterOrgLocations::withDefaultGroupCompanyOrg()->with('costCenter')->get()->map(function ($item) {
            $item->where('status', 'active');
           
            return [
                'id' => $item->costCenter->id,
                'name' => $item->costCenter->name,
            ];
        })->toArray();
        $dateRange = \Carbon\Carbon::parse($startDate)->format('d-m-Y') . " to " . \Carbon\Carbon::parse($endDate)->format('d-m-Y');
        $date2 = \Carbon\Carbon::parse($startDate)->format('jS-F-Y') . ' to ' . \Carbon\Carbon::parse($endDate)->format('jS-F-Y');

        return view('profitLoss.profitLoss',compact('cost_centers','organizations','organization','companies','organizationId','dateRange','date2'));
    }

    public function getPLInitialGroups(Request $r)
    {
        $currency="org";
        if ($r->currency!="") {
            $currency = $r->currency;
        };

        if ($r->date=="") {
            $financialYear = Helper::getFinancialYear(date('Y-m-d'));
            $startDate=$financialYear['start_date'];
            $endDate=$financialYear['end_date'];
        }
        else {
            $dates = explode(' to ', $r->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
        }

        $organizations=[];
        if ($r->organization_id && is_array($r->organization_id)) {
            $organizations = $r->organization_id;
        };
        if(count($organizations) == 0){
            $organizations[]=Helper::getAuthenticatedUser()->organization_id;
        }

        $data=Helper::getPlGroupsData($startDate,$endDate,$organizations,$currency,$r->cost_center_id,"profitloss");
        $details=Helper::getPlGroupDetails($data);

        return response()->json(['startDate'=>date('d-M-Y',strtotime($startDate)),'endDate'=>date('d-M-Y',strtotime($endDate)),'data'=>$details]);
    }

    public function getPLGroupLedgers(Request $r)
    {
        $currency="org";
        if ($r->currency!="") {
            $currency = $r->currency;
        };

        if ($r->date=="") {
            $financialYear = Helper::getFinancialYear(date('Y-m-d'));
            $startDate=$financialYear['start_date'];
            $endDate=$financialYear['end_date'];
        }
        else {
            $dates = explode(' to ', $r->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
        }

        $organizations=[];
        if ($r->organization_id && is_array($r->organization_id)) {
            $organizations = $r->organization_id;
        };
        if(count($organizations) == 0){
            $organizations[]=Helper::getAuthenticatedUser()->organization_id;
        }

        $data=$this->getGroupLedgers($r->id,$organizations,$startDate,$endDate,$currency,$r->cost_center_id);

        return response()->json(['data'=>$data]);
    }

    public function getPLGroupLedgersMultiple(Request $r)
    {
        $currency="org";
        if ($r->currency!="") {
            $currency = $r->currency;
        };

        if ($r->date=="") {
            $financialYear = Helper::getFinancialYear(date('Y-m-d'));
            $startDate=$financialYear['start_date'];
            $endDate=$financialYear['end_date'];
        }
        else {
            $dates = explode(' to ', $r->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
        }

        $organizations=[];
        if ($r->organization_id && is_array($r->organization_id)) {
            $organizations = $r->organization_id;
        };
        if(count($organizations) == 0){
            $organizations[]=Helper::getAuthenticatedUser()->organization_id;
        }

        $allData=[];
        foreach ($r->ids as $id) {
            $data=$this->getGroupLedgers($id,$organizations,$startDate,$endDate,$currency,$r->cost_center_id);

            $gData['id']=$id;
            $gData['data']=$data;
            $allData[]=$gData;
        }

        return response()->json(['data'=>$allData]);
    }

    public function getGroupLedgers($id,$organizations,$startDate,$endDate,$currency,$cost=null){
        $non_carry = Helper::getNonCarryGroups();
        $fy = Helper::getFinancialYear($startDate);
                            
        if(in_array($id,$non_carry))
        $carry=0;
        else 
        $carry=1;
        $plGroups=PLGroups::find($id);
        $groupIds=Group::whereIn('id',$plGroups->group_ids)->get();
        $childrens=[];
        $childrens=array_merge($childrens,$plGroups->group_ids);
        foreach ($groupIds as $groupId) {
            $childrens=array_merge($childrens,$groupId->getAllChildIds());
        }

        $data = Ledger::where( function($query) use ($childrens,$fy,$carry) {
            $query->whereIn('ledger_group_id', $childrens)
                  ->orWhere(function($subQuery) use ($childrens) {
                      foreach ($childrens as $child) {
                          $subQuery->orWhereJsonContains('ledger_group_id',(string)$child);
                      }
                  });
        })
        ->whereIn('organization_id', $organizations)
        ->where('status', 1)
        ->select('id', 'name','ledger_group_id')
        ->withSum(['details as details_sum_debit_amt' => function ($query) use ($startDate, $endDate,$childrens,$cost) {
                        $query->whereIn('ledger_parent_id',$childrens);
                        $query->when($cost, function ($query) use ($cost) {
                            $query->where('cost_center_id', $cost);
                        });
                        $query->withwhereHas('voucher', function ($query) use($startDate,$endDate) {
                        $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                        $query->whereBetween('document_date', [$startDate, $endDate]);
                        $query->orderBy('document_date', 'asc');
                    });
        }], "debit_amt_{$currency}")
        ->withSum(['details as details_sum_credit_amt' => function ($query) use ($startDate, $endDate,$childrens,$cost) {
            $query->whereIn('ledger_parent_id',$childrens);
            $query->when($cost, function ($query) use ($cost) {
                $query->where('cost_center_id', $cost);
            });
            $query->withwhereHas('voucher', function ($query) use($startDate,$endDate) {
                $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                $query->whereBetween('document_date', [$startDate, $endDate]);
                $query->orderBy('document_date', 'asc');
            });
        }], "credit_amt_{$currency}")
        ->with(['details' => function ($query) use ($startDate, $endDate,$childrens) {
            $query->whereIn('ledger_parent_id',$childrens);
            $query->withwhereHas('voucher', function ($query) use($startDate,$endDate) {
                $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                $query->whereBetween('document_date', [$startDate, $endDate]);
                $query->orderBy('document_date', 'asc');
            });
        }])
        ->get()
         ->map(function($ledger) use($childrens,$currency,$startDate,$organizations,$cost,$fy,$carry) {
                           
                    // Set to 0 if the sum is null
                    $ledger->details_sum_debit_amt = $ledger->details_sum_debit_amt ?? 0;
                    $ledger->details_sum_credit_amt = $ledger->details_sum_credit_amt ?? 0;

                    $closingText='';
                    
                    $openingData = ItemDetail::whereIn('ledger_parent_id',$childrens)
                    ->where('ledger_id',$ledger->id)
                    ->when($cost, function ($query) use ($cost) {
                        $query->where('cost_center_id', $cost);
                    })->withwhereHas('voucher', function ($query) use($startDate,$carry,$fy,$organizations) {
                    $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                    $query->where('document_date', '<', $startDate);
                        if(!$carry)
                        $query->where('document_date', '>=', $fy['start_date']);
                    $query->whereIn('organization_id', $organizations);
                    })
                    ->selectRaw("SUM(debit_amt_{$currency}) as total_debit, SUM(credit_amt_{$currency}) as total_credit")
                    ->first();
                
                    $opening = ($openingData->total_debit - $openingData->total_credit) ?? 0;

                    
                    
                    $closing= $opening +($ledger->details_sum_debit_amt-$ledger->details_sum_credit_amt);
                    if ($closing != 0) {
                        $closingText=$closing > 0 ? 'Dr' : 'Cr';
                    }
                    $ledger->closing = $closing < 0 ? -$closing : $closing .' '. $closingText;
                    $decodedLedgerGroupId = json_decode($ledger->ledger_group_id, true);

                 if (is_array($decodedLedgerGroupId)) {
                    $filteredValue = collect($decodedLedgerGroupId)->first(function ($item) use ($childrens) {
                        return in_array($item, $childrens);
                    });
                
                    $ledger->ledger_group_id = (int)$filteredValue ?? null; // Use found value or null if no match
                }    
                

                    unset($ledger->details);

                    return $ledger;
                });
                

                              
        return $data;
    }
}
