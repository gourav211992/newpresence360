<?php

namespace App\Http\Controllers\TermLoan;

use App\Models\ErpLoanAppraisal;
use App\Models\LoanDisbursement;
use App\Models\RecoveryLoan;
use App\Models\Book;
use App\Helpers\Helper;
use App\Models\HomeLoan;
use Carbon\Carbon;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\NumberPattern;
use App\Models\LoanManagement;
use App\Models\TermLoanAddress;
use App\Models\TermLoanDocument;
use App\Models\TermLoanNetWorth;
use App\Models\TermLoanPromoter;
use App\Models\InterestRateScore;
use App\Models\LoanApplicationLog;
use App\Models\TermLoanFinanceMean;
use App\Http\Controllers\Controller;
use App\Models\TermLoanConstitution;
use App\Models\TermLoanNetWorthProperty;
use App\Models\TermLoanNetWorthLiability;
use App\Models\TermLoanNetWorthExperience;
use App\Http\Requests\StoreTermLoanRequest;
use App\Models\TermLoanConstitutionPromoter;
use App\Models\TermLoanConstitutionPartnerDetail;

class TermLoanController extends Controller
{
    public function create(StoreTermLoanRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $concern_name = ($request->f_name ?? '') . ' ' . ($request->m_name ?? '') . ' ' . ($request->l_name ?? '');
        $promoter_name = ($request->f_name_pro ?? '') . ' ' . ($request->m_name_pro ?? '') . ' ' . ($request->l_name_pro ?? '');
        $edit_loanId = isset($request->edit_loanId) ? $request->edit_loanId : 0;
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }
        /* if ($edit_loanId == 0) {
            $appli_no = null;
            do {
                $appli_no = Helper::reGenerateDocumentNumber($request->series);
                $existingLoan = HomeLoan::where('appli_no', $appli_no)->first();
            } while ($existingLoan !== null);
            //dd('here', $appli_no);
        }*/
        // $status = $request->status_val == ConstantHelper::SUBMITTED ? Helper::checkApprovalRequired($request->series) : $request->status_val;
        $appli_no = $request->appli_no;
        $status = $request->status_val;

        $userData = Helper::userCheck();
        $TermLoan = HomeLoan::where('id', $edit_loanId)->first();

        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $organization_id = $organization->id;
        $group_id = $organization->group_id;
        $company_id = $organization->company_id;
        $loanable_id = Helper::getAuthenticatedUser()->auth_user_id;

        $TermLoan = HomeLoan::updateOrCreate([
            'id' => $edit_loanId
        ], [
            'organization_id' => $organization_id,
            'group_id' => $group_id,
            'company_id' => $company_id,
            // 'document_no' => $request->document_no,
            'doc_number_type' => $edit_loanId !== 0 ? $TermLoan->doc_number_type : $request->doc_number_type,
            'doc_reset_pattern' => $edit_loanId !== 0 ? $TermLoan->doc_reset_pattern : $request->doc_reset_pattern,
            'doc_prefix' => $edit_loanId !== 0 ? $TermLoan->doc_prefix : $request->doc_prefix,
            'doc_suffix' => $edit_loanId !== 0 ? $TermLoan->doc_suffix : $request->doc_suffix,
            'doc_no' => $edit_loanId !== 0 ? $TermLoan->doc_no : $request->doc_no,
            'document_date' => Carbon::now()->format('Y-m-d'),

            'type' => 3,
            'series' => $edit_loanId !== 0 ? $TermLoan->series : $request->series,
            'appli_no' => $edit_loanId !== 0 ? $TermLoan->appli_no : $appli_no,
            'book_id' => $edit_loanId !== 0 ? $TermLoan->series : $request->series,
            'ref_no' => $request->ref_no,
            'name' => $concern_name,
            'promoter_name' => $promoter_name,
            'loan_amount' => $request->loan_amount,
            'scheme_for' => $request->scheme_for,
            // 'status' => $request->status_val ?? 0,
            'activity_line' => $request->activity_line,
            'book_type_id' => $request->book_type,
            'email' => $request->tr_email ?? null,
            'approvalLevel'=>$edit_loanId !== 0 ? $TermLoan->approvalLevel : 1,
            'approvalStatus' => $status,
            'loanable_id' => $loanable_id,
            'loanable_type' => $userData['user_type']
        ]);
        LoanApplicationLog::logCreation($request, $TermLoan, 'term', $user->id);
        $address = $request->input('Address', []);
        if (count($address) > 0) {
            TermLoanAddress::updateOrCreate([
                'term_loan_id' => !empty($TermLoan->id) ? $TermLoan->id : $edit_loanId,
            ], [
                'term_loan_id' => $TermLoan->id,
                'co_term' => $address['co_term'] ?? null,
                'street_road' => $address['street_road'] ?? null,
                'house_land_mark' => $address['house_land_mark'] ?? null,
                'city_town_village' => $address['city_town_village'] ?? null,
                'pin_code' => $address['pin_code'] ?? null,
                'registered_offc_tele' => $address['registered_offc_tele'] ?? null,
                'registered_offc_mobile' => $address['registered_offc_mobile'] ?? null,
                'registered_offc_email_id' => $address['registered_offc_email_id'] ?? null,
                'registered_offc_fax_num' => $address['registered_offc_fax_num'] ?? null,
                'addr1' => $address['addr1'] ?? null,
                'addr2' => $address['addr2'] ?? null,
                'factory_tele' => $address['factory_tele'] ?? null,
                'factory_mobile' => $address['factory_mobile'] ?? null,
                'factory_email_id' => $address['factory_email_id'] ?? null,
                'factory_fax_num' => $address['factory_fax_num'] ?? null
            ]);
        }

        $term_promotor = $request->input('TermPromotor', []);
        if (count($term_promotor) > 0) {
            $domicile_photos = $request->file('TermPromotor.domicile_photo');
            $term_loan_val = TermLoanPromoter::withTrashed()->where('term_loan_id', $edit_loanId)->get();
            foreach ($term_loan_val as $val) {
                $val->forceDelete();
            }

            foreach ($term_promotor['promoter_name'] as $index => $promoter_name) {
                if ($index == 0) {
                    continue;
                }

                $image_customer = null;
                if (isset($domicile_photos[$index]) && !empty($domicile_photos[$index])) {
                    $path = $domicile_photos[$index]->store('loan_images', 'public');
                    $image_customer = $path;
                } elseif (!empty($term_promotor['stored_domicile_photo'][$index])) {
                    $image_customer = $term_promotor['stored_domicile_photo'][$index];
                } else {
                    $image_customer = null;
                }
                TermLoanPromoter::create([
                    'term_loan_id' => !empty($TermLoan->id) ? $TermLoan->id : $edit_loanId,
                    'promoter_name' => $term_promotor['promoter_name'][$index] ?? null,
                    'domicile' => $term_promotor['domicile'][$index] ?? null,
                    'domicile_photo' => $image_customer ?? null
                ]);
            }
        }

        $mean_finance = $request->input('MeanFinance', []);
        if (count($mean_finance) > 0) {
            TermLoanFinanceMean::updateOrCreate([
                'term_loan_id' => !empty($TermLoan->id) ? $TermLoan->id : $edit_loanId,
            ], [
                'term_loan_id' => $TermLoan->id,
                'promoters_cont' => $mean_finance['promoters_cont'] ?? null,
                'equity_total' => $mean_finance['equity_total'] ?? null,
                'midc_ltd' => $mean_finance['midc_ltd'] ?? null,
                'others' => $mean_finance['others'] ?? null,
                'debt_total' => $mean_finance['debt_total'] ?? null,
                'grand_total' => $mean_finance['grand_total'] ?? null,
                'guarantee_detail' => $mean_finance['guarantee_detail'] ?? null,
                'period_state' => $mean_finance['period_state'] ?? null,
                'primary_land' => $mean_finance['primary_land'] ?? null,
                'primary_building' => $mean_finance['primary_building'] ?? null,
                'primary_machinery' => $mean_finance['primary_machinery'] ?? null,
                'primary_other' => $mean_finance['primary_other'] ?? null,
                'primary_total' => $mean_finance['primary_total'] ?? null,
                'collateral_land' => $mean_finance['collateral_land'] ?? null,
                'collateral_building' => $mean_finance['collateral_building'] ?? null,
                'collateral_machinery' => $mean_finance['collateral_machinery'] ?? null,
                'collateral_other' => $mean_finance['collateral_other'] ?? null,
                'collateral_total' => $mean_finance['collateral_total'] ?? null
            ]);
        }

        $constitution = $request->input('Constitution', []);
        if (isset($constitution['common_data'])) {
            $term_loan_constitution = TermLoanConstitution::updateOrCreate([
                'term_loan_id' => !empty($TermLoan->id) ? $TermLoan->id : $edit_loanId,
            ], [
                'term_loan_id' => $TermLoan->id,
                'business_type' => $constitution['common_data']['cons_business_type'] ?? null,
                'prc' => $constitution['common_data']['prc'] ?? null,
                'esclation' => $constitution['common_data']['esclation'] ?? null,
                'sia_no' => $constitution['common_data']['sia_no'] ?? null,
                'sia_date' => $constitution['common_data']['sia_date'] ?? null,
                'director_name' => $constitution['common_data']['director_name'] ?? null,
                'working_capital' => $constitution['common_data']['working_capital'] ?? null,
                'capital_facilities' => $constitution['common_data']['capital_facilities'] ?? null,
                'site_dev' => $constitution['common_data']['site_dev'] ?? null,
                'civil_works' => $constitution['common_data']['civil_works'] ?? null,
                'plant_install' => $constitution['common_data']['plant_install'] ?? null,
                'technical_fee' => $constitution['common_data']['technical_fee'] ?? null,
                'fixed_asset' => $constitution['common_data']['fixed_asset'] ?? null,
                'pre_operative' => $constitution['common_data']['pre_operative'] ?? null,
                'provision' => $constitution['common_data']['provision'] ?? null,
                'startup_expense' => $constitution['common_data']['startup_expense'] ?? null,
                'margin_money' => $constitution['common_data']['margin_money'] ?? null,
                'total' => $constitution['common_data']['cons_total'] ?? null
            ]);

            if ($term_loan_constitution) {
                if (isset($constitution['sister_concern'])) {
                    $term_loan_constitution_D = TermLoanConstitutionPromoter::withTrashed()->where('term_loan_constitution_id', $term_loan_constitution->id)->get();
                    foreach ($term_loan_constitution_D as $val) {
                        $val->forceDelete();
                    }
                    foreach ($constitution['sister_concern'] as $index => $policy_no) {
                        if ($index == 0) {
                            continue;
                        }
                        TermLoanConstitutionPromoter::create([
                            'term_loan_constitution_id' => $term_loan_constitution->id,
                            'sister_concern' => $constitution['sister_concern'][$index] ?? null,
                            'banker_name' => $constitution['banker_name'][$index] ?? null,
                            'nature_facility_address' => $constitution['nature_facility_address'][$index] ?? null,
                            'outstanding' => $constitution['outstanding'][$index] ?? null,
                            'any_default' => $constitution['any_default'][$index] ?? null
                        ]);
                    }
                }

                if (!empty($constitution['par_name'])) {
                    $term_loan_constitution_F = TermLoanConstitutionPartnerDetail::withTrashed()->where('term_loan_constitution_id', $term_loan_constitution->id)->get();
                    foreach ($term_loan_constitution_F as $val) {
                        $val->forceDelete();
                    }
                    foreach ($constitution['par_name'] as $index => $post_office) {
                        if ($index == 0) {
                            continue;
                        }
                        TermLoanConstitutionPartnerDetail::create([
                            'term_loan_constitution_id' => $term_loan_constitution->id,
                            'name' => $constitution['par_name'][$index] ?? null,
                            'age' => $constitution['par_age'][$index] ?? null,
                            'position' => $constitution['par_position'][$index] ?? null,
                            'shareholding' => $constitution['par_shareholding'][$index] ?? null,
                            'percentage' => $constitution['par_percentage'][$index] ?? null
                        ]);
                    }
                }
            }
        }

        $term_netWorth = $request->input('TermNetWorth', []);
        if (isset($term_netWorth['common_data'])) {
            $asset_proof_data = $request->file('TermNetWorth.common_data.asset_proof');
            $image_customer = null;
            if (!empty($asset_proof_data)) {
                $path = $asset_proof_data->store('loan_images', 'public');
                $image_customer = $path;
            } elseif (!empty($term_netWorth['common_data']['stored_asset_proof2'])) {
                $image_customer = $term_netWorth['common_data']['stored_asset_proof2'];
            } else {
                $image_customer = null;
            }
            $term_loan_net_worth = TermLoanNetWorth::updateOrCreate([
                'term_loan_id' => !empty($TermLoan->id) ? $TermLoan->id : $edit_loanId,
            ], [
                'term_loan_id' => $TermLoan->id,
                'name' => $term_netWorth['common_data']['nw_name'] ?? null,
                'father_name' => $term_netWorth['common_data']['nw_father_name'] ?? null,
                'dob' => $term_netWorth['common_data']['nw_dob'] ?? null,
                'unit_address1' => $term_netWorth['common_data']['nw_unit_address1'] ?? null,
                'unit_address2' => $term_netWorth['common_data']['nw_unit_address2'] ?? null,
                'unit_phone' => $term_netWorth['common_data']['nw_unit_phone'] ?? null,
                'resi_address1' => $term_netWorth['common_data']['nw_resi_address1'] ?? null,
                'resi_address2' => $term_netWorth['common_data']['nw_resi_address2'] ?? null,
                'resi_mobile' => $term_netWorth['common_data']['nw_resi_mobile'] ?? null,
                'resi_phone' => $term_netWorth['common_data']['nw_resi_phone'] ?? null,
                'qualification' => $term_netWorth['common_data']['nw_qualification'] ?? null,
                'present_profession' => $term_netWorth['common_data']['nw_present_profession'] ?? null,
                'passport_holder' => $term_netWorth['common_data']['nw_passport_holder'] ?? null,
                'permanent_acc' => $term_netWorth['common_data']['nw_permanent_acc'] ?? null,
                'income_declare' => $term_netWorth['common_data']['nw_income_declare'] ?? null,
                'bank_address' => $term_netWorth['common_data']['nw_bank_address'] ?? null,
                'opening_bank_date' => $term_netWorth['common_data']['nw_opening_bank_date'] ?? null,
                'club_member' => $term_netWorth['common_data']['nw_club_member'] ?? null,
                'club_name' => $term_netWorth['common_data']['nw_club_name'] ?? null,
                'club_address' => $term_netWorth['common_data']['nw_club_address'] ?? null,
                'paid_membership_fee' => $term_netWorth['common_data']['nw_paid_membership_fee'] ?? null,
                'cash_on_hold' => $term_netWorth['common_data']['nw_cash_on_hold'] ?? null,
                'bank_cash' => $term_netWorth['common_data']['nw_bank_cash'] ?? null,
                'bank_investment' => $term_netWorth['common_data']['nw_bank_investment'] ?? null,
                'bank_deposit' => $term_netWorth['common_data']['nw_bank_deposit'] ?? null,
                'bank_shares' => $term_netWorth['common_data']['nw_bank_shares'] ?? null,
                'jewelery' => $term_netWorth['common_data']['nw_jewelery'] ?? null,
                'moveable_asset' => $term_netWorth['common_data']['nw_moveable_asset'] ?? null,
                'moveable_sub_total' => $term_netWorth['common_data']['nw_moveable_sub_total'] ?? null,
                'moveable_total' => $term_netWorth['common_data']['nw_moveable_total'] ?? null,
                'total_net_worth' => $term_netWorth['common_data']['nw_total_net_worth'] ?? null,
                'asset_proof' => $image_customer
            ]);

            if ($term_loan_net_worth) {
                if (isset($term_netWorth['nw_no_of_years'])) {
                    $doc_photos = $request->file('TermNetWorth.nw_doc');
                    $term_netWorth_D = TermLoanNetWorthExperience::withTrashed()->where('term_loan_net_worth_id', $term_loan_net_worth->id)->get();
                    foreach ($term_netWorth_D as $val) {
                        $val->forceDelete();
                    }
                    foreach ($term_netWorth['nw_no_of_years'] as $index => $no_of_years) {
                        if ($index == 0) {
                            continue;
                        }
                        $image_customer = null;
                        if (isset($doc_photos[$index]) && !empty($doc_photos[$index])) {
                            $path = $doc_photos[$index]->store('loan_documents', 'public');
                            $image_customer = $path;
                        } elseif (!empty($term_netWorth['stored_nw_doc'][$index])) {
                            $image_customer = $term_netWorth['stored_nw_doc'][$index];
                        } else {
                            $image_customer = null;
                        }
                        TermLoanNetWorthExperience::create([
                            'term_loan_net_worth_id' => $term_loan_net_worth->id,
                            'no_of_years' => $term_netWorth['nw_no_of_years'][$index] ?? null,
                            'employer' => $term_netWorth['nw_employer'][$index] ?? null,
                            'designation' => $term_netWorth['nw_designation'][$index] ?? null,
                            'last_salary_drawn' => $term_netWorth['nw_last_salary_drawn'][$index] ?? null,
                            'doc' => $image_customer
                        ]);
                    }
                }

                if (isset($term_netWorth['nw_property_desc'])) {
                    $term_netWorth_f = TermLoanNetWorthProperty::withTrashed()->where('term_loan_net_worth_id', $term_loan_net_worth->id)->get();
                    foreach ($term_netWorth_f as $val) {
                        $val->forceDelete();
                    }
                    foreach ($term_netWorth['nw_property_desc'] as $index => $property_desc) {
                        if ($index == 0) {
                            continue;
                        }
                        TermLoanNetWorthProperty::create([
                            'term_loan_net_worth_id' => $term_loan_net_worth->id,
                            'property_desc' => $term_netWorth['nw_property_desc'][$index] ?? null,
                            'property_value' => $term_netWorth['nw_property_value'][$index] ?? null,
                            'acquired' => $term_netWorth['nw_acquired'][$index] ?? null
                        ]);
                    }
                }

                if (isset($term_netWorth['nw_net_worth_desc'])) {
                    $term_netWorth_e = TermLoanNetWorthLiability::withTrashed()->where('term_loan_net_worth_id', $term_loan_net_worth->id)->get();
                    foreach ($term_netWorth_e as $val) {
                        $val->forceDelete();
                    }
                    foreach ($term_netWorth['nw_net_worth_desc'] as $index => $nw_net_worth_desc) {
                        if ($index == 0) {
                            continue;
                        }
                        TermLoanNetWorthLiability::create([
                            'term_loan_net_worth_id' => $term_loan_net_worth->id,
                            'net_worth_desc' => $term_netWorth['nw_net_worth_desc'][$index] ?? null,
                            'net_worth_value' => $term_netWorth['nw_net_worth_value'][$index] ?? null
                        ]);
                    }
                }
            }
        }

        $term_loan_document = $request->TermLoanDocument ?? [];
        $adhar_card = null;
        $gir_no = null;
        $asset_proof = null;
        $application = null;
        foreach (['adhar_card', 'gir_no', 'asset_proof', 'application'] as $key => $val) {
            $val_data = $term_loan_document['common_data'][$val] ?? null;
            $multi_files = [];
            if ($val_data) {
                foreach ($val_data as $file) {
                    $filePath = $file->store('loan_documents', 'public');
                    $multi_files[] = $filePath;
                }
            }

            if ($val == 'adhar_card') {
                $stored_adhar_card = $term_loan_document['common_data']['stored_adhar_card'] ?? null;
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

            if ($val == 'gir_no') {
                $stored_gir_no = $term_loan_document['common_data']['stored_gir_no'] ?? null;
                if (count($multi_files) == 0) {
                    if (!empty($stored_gir_no)) {
                        $gir_no = $stored_gir_no;
                    } else {
                        $gir_no = '[]';
                    }
                } else {
                    $gir_no = json_encode($multi_files);
                }
            }

            if ($val == 'asset_proof') {
                $stored_asset_proof = $term_loan_document['common_data']['stored_asset_proof'] ?? null;
                if (count($multi_files) == 0) {
                    if (!empty($stored_asset_proof)) {
                        $asset_proof = $stored_asset_proof;
                    } else {
                        $asset_proof = '[]';
                    }
                } else {
                    $asset_proof = json_encode($multi_files);
                }
            }

            if ($val == 'application') {
                $stored_application = $term_loan_document['common_data']['stored_application'] ?? null;
                if (count($multi_files) == 0) {
                    if (!empty($stored_application)) {
                        $application = $stored_application;
                    } else {
                        $application = '[]';
                    }
                } else {
                    $application = json_encode($multi_files);
                }
            }
        }
        if (isset($term_loan_document['common_data'])) {
            TermLoanDocument::updateOrCreate([
                'term_loan_id' => !empty($TermLoan->id) ? $TermLoan->id : $edit_loanId,
            ], [
                'term_loan_id' => $TermLoan->id,
                'adhar_card' => $adhar_card,
                'gir_no' => $gir_no,
                'asset_proof' => $asset_proof,
                'application' => $application
            ]);
        }

        $organization = Organization::getOrganization();
        $book_type = (int) $request->series;
        if (!isset($request->edit_loanId)) {
            if ($organization) {
                NumberPattern::incrementIndex($organization->id, $book_type);
            }
        }

        Helper::logs(
            $request->series,
            $request->appli_no,
            $TermLoan->id,
            $organization->id,
            'Term Loan',
            '-',
            $TermLoan->type,
            '-',
            $TermLoan->loanable_type,
            0,
            $TermLoan->created_at,
            $TermLoan->approvalStatus
        );

        return redirect("loan/my-application")->with('success', 'Term Loan created/updated successfully!');
    }


    public function viewTermDetail($id)
    {
        $user = Helper::getAuthenticatedUser();
        $termLoan = TermLoanPromoter::fetchRecord($id);
        $user = Helper::getAuthenticatedUser();
        $series = Book::where('organization_id', $user->organization_id)->select('id', 'book_name')->get();
        $parentURL = "loan_term-loan";

         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();
        if ($termLoan && $termLoan->loanApplicationLog) {
            $logs = $termLoan->loanApplicationLog->sortByDesc('id');
            $logsGroupedByStatus = $logs->groupBy('action_type');
        } else {
            $logsGroupedByStatus = [];
        }
        $view_detail = 1;
        $interest_rate = '';
        if (!empty($termLoan->ass_cibil)) {
            $interest_rate = InterestRateScore::where('cibil_score_min', '<=', $termLoan->ass_cibil)
                ->where('cibil_score_max', '>=', $termLoan->ass_cibil)
                ->select('interest_rate')
                ->first();
        }
        $page = "view_detail";

        $overview = ErpLoanAppraisal::with('loan', 'disbursal', 'recovery', 'dpr')->where('loan_id', $id)->first();
        $loan_disbursement = LoanDisbursement::with('homeLoan')->where('home_loan_id', $id)->get();
        $recovery_loan = RecoveryLoan::with('homeLoan')->where('home_loan_id', $id)->get();

        $document_listing = Helper::documentListing($id);
        $termLoan->loanable_type = strtolower(class_basename($termLoan->loanable_type));

        $buttons = Helper::actionButtonDisplayForLoan($termLoan->series, $termLoan->approvalStatus,$termLoan->id,$termLoan->loan_amount,$termLoan->approval_level,$termLoan->loanable_id,$termLoan->loanable_type);
        $logs = Helper::getLogs($id);

        return view('loan.term_loan', compact('termLoan', 'series', 'book_type', 'logsGroupedByStatus', 'view_detail', 'interest_rate', 'page', 'overview', 'loan_disbursement', 'recovery_loan', 'document_listing', 'buttons', 'logs'));
    }

    public function editTermDetail($id)
    {
        $user = Helper::getAuthenticatedUser();
        $termLoan = TermLoanPromoter::fetchRecord($id);
        $editData = 1;
        $user = Helper::getAuthenticatedUser();
        $series = Book::where('organization_id', $user->organization_id)->select('id', 'book_name')->get();
        $parentURL = "loan_term-loan";

         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();
        $creatorType = explode("\\", $termLoan->loanable_type);
        $termLoan->loanable_type = strtolower(class_basename($termLoan->loanable_type));

        $buttons = Helper::actionButtonDisplayForLoan($termLoan->series, $termLoan->approvalStatus,$termLoan->id,$termLoan->loan_amount,$termLoan->approval_level,$termLoan->loanable_id,$termLoan->loanable_type);
        $history = Helper::getApprovalHistory($termLoan->series, $id, 0);
        $logs = Helper::getLogs($id);
        $page = "edit";
        return view('loan.term_loan', compact('termLoan', 'editData', 'series', 'book_type', 'buttons', 'history', 'page', 'logs'));
    }

    public function destroy($id)
    {
        $termLoan = TermLoanPromoter::deleteHomeLoanAndRelatedRecords($id);
        return redirect("loan/my-application")->with('success', 'Term Loan and related records deleted successfully.');
    }
}
