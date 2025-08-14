<?php

namespace App\Http\Controllers\VehicleLoan;

use App\Models\ErpLoanAppraisal;
use App\Models\LoanDisbursement;
use App\Models\RecoveryLoan;
use Carbon\Carbon;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper;
use App\Models\HomeLoan;
use App\Models\VehicleLoan;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\NumberPattern;
use App\Models\LoanManagement;
use App\Models\InterestRateScore;
use App\Models\LoanApplicationLog;
use App\Models\LoanGuarantorParty;
use App\Models\LoanVehicleDocument;
use App\Models\VehicleBankSecurity;
use App\Http\Controllers\Controller;
use App\Models\LoanVehicleSchemeCost;
use App\Models\LoanFinanceLoanSecurity;
use App\Models\LoanGuarantorPartyAddress;
use App\Http\Requests\StoreVehicleLoanRequest;

class VehicleLoanController extends Controller
{
    public function create(StoreVehicleLoanRequest $request)
    {
//        dd(Helper::getAuthenticatedUser()->auth_user_id);
        $user = Helper::getAuthenticatedUser();
        $name = ($request->f_name ?? '') . ' ' . ($request->m_name ?? '') . ' ' . ($request->l_name ?? '');
        $edit_loanId = isset($request->edit_loanId) ? $request->edit_loanId : 0;
        $image_customer = null;
        if ($request->has('image')) {
            $path = $request->file('image')->store('loan_images', 'public');
            $image_customer = $path;
        } elseif ($request->has('stored_image')) {
            $image_customer = $request->stored_image;
        } else {
            $image_customer = null;
        }
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }
        /*if ($edit_loanId == 0) {
            do {
                $appli_no = Helper::reGenerateDocumentNumber($request->series);
                $existingLoan = HomeLoan::where('appli_no', $appli_no)->first();
            } while ($existingLoan !== null);
            //dd('here', $appli_no);
        }*/
        $appli_no = $request->appli_no;
        // $status = $request->status_val == ConstantHelper::SUBMITTED ? Helper::checkApprovalRequired($request->series) : $request->status_val;
        $status = $request->status_val;

        $userData = Helper::userCheck();
        $VehicleLoan = HomeLoan::where('id', $edit_loanId)->first();

        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $organization_id = $organization->id;
        $group_id = $organization->group_id;
        $company_id = $organization->company_id;
        $loanable_id = Helper::getAuthenticatedUser()->auth_user_id;

        $VehicleLoan = HomeLoan::updateOrCreate([
            'id' => $edit_loanId
        ], [
            'organization_id' => $organization_id,
            'group_id' => $group_id,
            'company_id' => $company_id,
            // 'document_no' => $request->document_no,
            'doc_number_type' => $edit_loanId !== 0 ? $VehicleLoan->doc_number_type : $request->doc_number_type,
            'doc_reset_pattern' => $edit_loanId !== 0 ? $VehicleLoan->doc_reset_pattern : $request->doc_reset_pattern,
            'doc_prefix' => $edit_loanId !== 0 ? $VehicleLoan->doc_prefix : $request->doc_prefix,
            'doc_suffix' => $edit_loanId !== 0 ? $VehicleLoan->doc_suffix : $request->doc_suffix,
            'doc_no' => $edit_loanId !== 0 ? $VehicleLoan->doc_no : $request->doc_no,
            'document_date' => Carbon::now()->format('Y-m-d'),
            'type' => 2,
            'series' => $edit_loanId !== 0 ? $VehicleLoan->series : $request->series,
            'book_id' => $edit_loanId !== 0 ? $VehicleLoan->series : $request->series,
            'appli_no' => $edit_loanId !== 0 ? $VehicleLoan->appli_no : $appli_no,
            'ref_no' => $request->ref_no,
            'loan_amount' => $request->loan_amount,
            'name' => $name,
            'address' => $request->address,
            'mobile' => $request->mobile,
            'telex' => $request->telex,
            'constitution' => $request->constitution,
            'scheduled_tribe' => $request->scheduled_tribe,
            'partner' => $request->Proprietor,
            'Partner_ship' => $request->Partnership,
            'approvalLevel'=>$edit_loanId !== 0 ? $VehicleLoan->approvalLevel : 1,
            'approvalStatus' => $status,
            'image' => $image_customer,
            'book_type_id' => $request->book_type,
            'email' => $request->ve_email ?? null,
            'loanable_id' => $loanable_id,
            'loanable_type' => $userData['user_type']
        ]);
        LoanApplicationLog::logCreation($request, $VehicleLoan, 'vehicle', $user->id);
        $vehicleData = $request->input('vehicleLoan', []);
        if (count($vehicleData) > 0) {
            $vehicle_l = VehicleLoan::withTrashed()->where('vehicle_id', $edit_loanId)->get();
            foreach ($vehicle_l as $val) {
                $val->forceDelete();
            }
            foreach ($vehicleData['v_name'] as $index => $bank_name) {
                if ($index == 0) {
                    continue;
                }
                VehicleLoan::create([
                    'vehicle_id' => !empty($VehicleLoan->id) ? $VehicleLoan->id : $edit_loanId,
                    'name' => $vehicleData['v_name'][$index] ?? null,
                    'address' => $vehicleData['v_address'][$index] ?? null,
                    'father_name' => $vehicleData['v_father_name'][$index] ?? null,
                    'qualification' => $vehicleData['v_quali'][$index] ?? null,
                    'investment' => $vehicleData['v_inves'][$index] ?? null
                ]);
            }
        }

        $bank_Security = $request->input('BankSecurity', []);
        if (isset($bank_Security['common_data'])) {
            VehicleBankSecurity::updateOrCreate([
                'vehicle_id' => !empty($VehicleLoan->id) ? $VehicleLoan->id : $edit_loanId,
            ], [
                'vehicle_id' => $VehicleLoan->id,
                'opening_acc' => $bank_Security['common_data']['opening_acc'] ?? null,
                'bank_name1' => $bank_Security['common_data']['bank_name1'] ?? null,
                'bank_addr1' => $bank_Security['common_data']['bank_addr1'] ?? null,
                'bank_name2' => $bank_Security['common_data']['bank_name2'] ?? null,
                'bank_addr2' => $bank_Security['common_data']['bank_addr2'] ?? null,
                'acc_nature' => $bank_Security['common_data']['acc_nature'] ?? null,
                'borrowing_detail' => $bank_Security['common_data']['borrowing_detail'] ?? null,
                'security_offerd' => $bank_Security['common_data']['security_offerd'] ?? null,
                'security_desc' => $bank_Security['common_data']['security_desc'] ?? null,
                'security_market_val' => $bank_Security['common_data']['security_market_val'] ?? null
            ]);
        }

        $vehicleScheme = $request->input('VehicleScheme', []);
        if (isset($vehicleScheme['common_data'])) {
            LoanVehicleSchemeCost::updateOrCreate([
                'vehicle_id' => !empty($VehicleLoan->id) ? $VehicleLoan->id : $edit_loanId,
            ], [
                'vehicle_id' => $VehicleLoan->id,
                'model' => $vehicleScheme['common_data']['model'] ?? null,
                'make' => $vehicleScheme['common_data']['make'] ?? null,
                'h_p' => $vehicleScheme['common_data']['h_p'] ?? null,
                'carry_capacity' => $vehicleScheme['common_data']['carry_capacity'] ?? null,
                'classic_vessel' => $vehicleScheme['common_data']['classic_vessel'] ?? null,
                'body_building' => $vehicleScheme['common_data']['body_building'] ?? null,
                'other_item' => $vehicleScheme['common_data']['other_item'] ?? null,
                'spares_tyres' => $vehicleScheme['common_data']['spares_tyres'] ?? null,
                'insurance_taxes' => $vehicleScheme['common_data']['insurance_taxes'] ?? null,
                'pre_operative_exp' => $vehicleScheme['common_data']['pre_operative_exp'] ?? null,
                'working_c_margin' => $vehicleScheme['common_data']['working_c_margin'] ?? null,
                'total' => $vehicleScheme['common_data']['total'] ?? null
            ]);
        }

        $financeSecurity = $request->input('FinanceSecurity', []);
        if (isset($financeSecurity['common_data'])) {
            LoanFinanceLoanSecurity::updateOrCreate([
                'vehicle_id' => !empty($VehicleLoan->id) ? $VehicleLoan->id : $edit_loanId,
            ], [
                'vehicle_id' => $VehicleLoan->id,
                'own_capital' => $financeSecurity['common_data']['own_capital'] ?? null,
                'term_midc' => $financeSecurity['common_data']['term_midc'] ?? null,
                'finance_total' => $financeSecurity['common_data']['finance_total'] ?? null,
                'vehicle' => $financeSecurity['common_data']['vehicle'] ?? null,
                'collateral_security' => $financeSecurity['common_data']['collateral_security'] ?? null,
                'security_total' => $financeSecurity['common_data']['security_total'] ?? null
            ]);
        }

        $guarantorParty = $request->input('GuarantorParty', []);
        if (count($guarantorParty) > 0) {
            $vehicle_guar_l = LoanGuarantorParty::withTrashed()->where('vehicle_id', $edit_loanId)->get();
            foreach ($vehicle_guar_l as $val) {
                $val->forceDelete();
            }
            foreach ($guarantorParty['guarantor_name'] as $index => $guarantor_name) {
                if ($index == 0) {
                    continue;
                }
                LoanGuarantorParty::create([
                    'vehicle_id' => !empty($VehicleLoan->id) ? $VehicleLoan->id : $edit_loanId,
                    'guarantor_name' => $guarantorParty['guarantor_name'][$index] ?? null,
                    'address' => $guarantorParty['address'][$index] ?? null
                ]);
            }
        }

        $guarantorPartyAddress = $request->input('GuarantorPartyAddress', []);
        if (count($guarantorPartyAddress) > 0) {
            $vehicle_guar_add_l = LoanGuarantorPartyAddress::withTrashed()->where('vehicle_id', $edit_loanId)->get();
            foreach ($vehicle_guar_add_l as $val) {
                $val->forceDelete();
            }
            foreach ($guarantorPartyAddress['party_name'] as $index => $party_name) {
                if ($index == 0) {
                    continue;
                }
                LoanGuarantorPartyAddress::create([
                    'vehicle_id' => !empty($VehicleLoan->id) ? $VehicleLoan->id : $edit_loanId,
                    'party_name' => $guarantorPartyAddress['party_name'][$index] ?? null,
                    'address' => $guarantorPartyAddress['address'][$index] ?? null
                ]);
            }
        }

        $loan_document = $request->LoanDocument ?? [];
        $adhar_card = null;
        $pan_gir_no = null;
        $vehicle_doc = null;
        $security_doc = null;
        $partnership_doc = null;
        $affidavit_doc = null;
        $scan_doc = null;
        foreach (['adhar_card', 'pan_gir_no', 'vehicle_doc', 'security_doc', 'partnership_doc', 'partnership_doc', 'affidavit_doc', 'scan_doc'] as $key => $val) {
            $val_data = $loan_document['common_data'][$val] ?? null;
            $multi_files = [];
            if ($val_data) {
                foreach ($val_data as $file) {
                    $filePath = $file->store('loan_documents', 'public');
                    $multi_files[] = $filePath;
                }
            }

            if ($val == 'adhar_card') {
                $stored_adhar_card = $loan_document['common_data']['stored_adhar_card'] ?? null;
                if (count($multi_files) == 0) {
                    if (!empty($stored_adhar_card)) {
                        $adhar_card = $stored_adhar_card;
                    } else {
                        $adhar_card = '[]';
                    }
                } else {
                    $adhar_card = json_encode($multi_files);
                }
            }

            if ($val == 'pan_gir_no') {
                $stored_pan_gir_no = $loan_document['common_data']['stored_pan_gir_no'] ?? null;
                if (count($multi_files) == 0) {
                    if (!empty($stored_pan_gir_no)) {
                        $pan_gir_no = $stored_pan_gir_no;
                    } else {
                        $pan_gir_no = '[]';
                    }
                } else {
                    $pan_gir_no = json_encode($multi_files);
                }
            }

            if ($val == 'vehicle_doc') {
                $stored_vehicle_doc = $loan_document['common_data']['stored_vehicle_doc'] ?? null;
                if (count($multi_files) == 0) {
                    if (!empty($stored_vehicle_doc)) {
                        $vehicle_doc = $stored_vehicle_doc;
                    } else {
                        $vehicle_doc = '[]';
                    }
                } else {
                    $vehicle_doc = json_encode($multi_files);
                }
            }

            if ($val == 'security_doc') {
                $stored_security_doc = $loan_document['common_data']['stored_security_doc'] ?? null;
                if (count($multi_files) == 0) {
                    if (!empty($stored_security_doc)) {
                        $security_doc = $stored_security_doc;
                    } else {
                        $security_doc = '[]';
                    }
                } else {
                    $security_doc = json_encode($multi_files);
                }
            }

            if ($val == 'partnership_doc') {
                $stored_partnership_doc = $loan_document['common_data']['stored_partnership_doc'] ?? null;
                if (count($multi_files) == 0) {
                    if (!empty($stored_partnership_doc)) {
                        $partnership_doc = $stored_partnership_doc;
                    } else {
                        $partnership_doc = '[]';
                    }
                } else {
                    $partnership_doc = json_encode($multi_files);
                }
            }

            if ($val == 'affidavit_doc') {
                $stored_affidavit_doc = $loan_document['common_data']['stored_affidavit_doc'] ?? null;
                if (count($multi_files) == 0) {
                    if (!empty($stored_affidavit_doc)) {
                        $affidavit_doc = $stored_affidavit_doc;
                    } else {
                        $affidavit_doc = '[]';
                    }
                } else {
                    $affidavit_doc = json_encode($multi_files);
                }
            }

            if ($val == 'scan_doc') {
                $stored_scan_doc = $loan_document['common_data']['stored_scan_doc'] ?? null;
                if (count($multi_files) == 0) {
                    if (!empty($stored_scan_doc)) {
                        $scan_doc = $stored_scan_doc;
                    } else {
                        $scan_doc = '[]';
                    }
                } else {
                    $scan_doc = json_encode($multi_files);
                }
            }
        }
        if (isset($loan_document['common_data'])) {
            LoanVehicleDocument::updateOrCreate([
                'vehicle_id' => !empty($VehicleLoan->id) ? $VehicleLoan->id : $edit_loanId,
            ], [
                'vehicle_id' => $VehicleLoan->id,
                'adhar_card' => $adhar_card,
                'pan_gir_no' => $pan_gir_no,
                'vehicle_doc' => $vehicle_doc,
                'security_doc' => $security_doc,
                'partnership_doc' => $partnership_doc,
                'affidavit_doc' => $affidavit_doc,
                'scan_doc' => $scan_doc
            ]);
        }

        $organization = Organization::getOrganization();
        if (!isset($request->edit_loanId)) {
            if ($organization) {
                $book_type = (int) $request->series;
                NumberPattern::incrementIndex($organization->id, $book_type);
            }
        }

        Helper::logs(
            $request->series,
            $request->appli_no,
            $VehicleLoan->id,
            $organization->id,
            'Vehicle Loan',
            '-',
            $VehicleLoan->type,
            '-',
            $VehicleLoan->loanable_type,
            0,
            $VehicleLoan->created_at,
            $VehicleLoan->approvalStatus
        );

        return redirect("loan/my-application")->with('success', 'Vehicle Loan created/updated successfully!');
    }


    public function viewVehicleDetail($id)
    {
        $user = Helper::getAuthenticatedUser();
        $vehicleLoan = VehicleLoan::fetchRecord($id);
        $user = Helper::getAuthenticatedUser();
        $series = Book::where('organization_id', $user->organization_id)->select('id', 'book_name')->get();
        $parentURL = "loan_vehicle-loan";

         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();

        if ($vehicleLoan && $vehicleLoan->loanApplicationLog) {
            $logs = $vehicleLoan->loanApplicationLog->sortByDesc('id');
            $logsGroupedByStatus = $logs->groupBy('action_type');
        } else {
            $logsGroupedByStatus = [];
        }
        $view_detail = 1;
        $interest_rate = '';
        if (!empty($vehicleLoan->ass_cibil)) {
            $interest_rate = InterestRateScore::where('cibil_score_min', '<=', $vehicleLoan->ass_cibil)
                ->where('cibil_score_max', '>=', $vehicleLoan->ass_cibil)
                ->select('interest_rate')
                ->first();
        }
        $page = "view_detail";

        $overview = ErpLoanAppraisal::with('loan', 'disbursal', 'recovery', 'dpr')->where('loan_id', $id)->first();
        $loan_disbursement = LoanDisbursement::with('homeLoan')->where('home_loan_id', $id)->get();
        $recovery_loan = RecoveryLoan::with('homeLoan')->where('home_loan_id', $id)->get();

        $document_listing = Helper::documentListing($id);

        $vehicleLoan->loanable_type = strtolower(class_basename($vehicleLoan->loanable_type));
        $buttons = Helper::actionButtonDisplayForLoan($vehicleLoan->series, $vehicleLoan->approvalStatus,$vehicleLoan->id,$vehicleLoan->loan_amount,$vehicleLoan->approval_level,$vehicleLoan->loanable_id,$vehicleLoan->loanable_type);         $logs = Helper::getLogs($id);

        return view('loan.vehicle_loan', compact('vehicleLoan', 'series', 'book_type', 'logsGroupedByStatus', 'view_detail', 'interest_rate', 'page', 'overview', 'loan_disbursement', 'recovery_loan', 'document_listing', 'buttons', 'logs'));
    }

    public function editVehicleDetail($id)
    {
        $user = Helper::getAuthenticatedUser();
        $vehicleLoan = VehicleLoan::fetchRecord($id);
        $editData = 1;
        $user = Helper::getAuthenticatedUser();
        $series = Book::where('organization_id', $user->organization_id)->select('id', 'book_name')->get();
        $parentURL = "loan_vehicle-loan";

         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();

        $creatorType = explode("\\", $vehicleLoan->loanable_type);
        $vehicleLoan->loanable_type = strtolower(class_basename($vehicleLoan->loanable_type));
        $buttons = Helper::actionButtonDisplayForLoan($vehicleLoan->series, $vehicleLoan->approvalStatus,$vehicleLoan->id,$vehicleLoan->loan_amount,$vehicleLoan->approval_level,$vehicleLoan->loanable_id,$vehicleLoan->loanable_type);          $history = Helper::getApprovalHistory($vehicleLoan->series, $id, 0);

        $page = "edit";
        return view('loan.vehicle_loan', compact('vehicleLoan', 'editData', 'series', 'book_type', 'buttons', 'history', 'page'));
    }

    public function destroy($id)
    {
        $vehicleLoan = VehicleLoan::deleteHomeLoanAndRelatedRecords($id);
        return redirect("loan/my-application")->with('success', 'Vehicle loan and related records deleted successfully.');
    }
}
