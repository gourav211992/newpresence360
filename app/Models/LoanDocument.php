<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanDocument extends Model
{
    protected $table = 'erp_loan_documents';

    use HasFactory;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'home_loan_id');
    }

    public static function createUpdateDocument($request, $edit_loanId, $homeLoan){
        $loan_document = $request->LoanDocument ?? [];
        $adhar_card = null;
        $gir_no = null;
        $plot_doc = null;
        $land_doc = null;
        $income_proof = null;
        foreach (['adhar_card', 'gir_no', 'plot_doc', 'land_doc','income_proof'] as $key => $val) {
            $val_data = $loan_document['common_data'][$val] ?? null;
            $multi_files = [];
            if($val_data){
                foreach ($val_data as $file) {
                    $filePath = $file->store('loan_documents', 'public');
                    $multi_files[] = $filePath;
                }
            }

            if($val == 'adhar_card'){
                $stored_adhar_card = $loan_document['common_data']['stored_adhar_card'] ?? null;
                if(count($multi_files) == 0){
                    if(!empty($stored_adhar_card)){
                        $adhar_card = $stored_adhar_card;
                    }else{
                        $adhar_card = '[]';
                    }
                }else{
                    $adhar_card = json_encode($multi_files);
                }
            }
            
            if($val == 'gir_no'){
                $stored_gir_no = $loan_document['common_data']['stored_gir_no'] ?? null;
                if(count($multi_files) == 0){
                    if(!empty($stored_gir_no)){
                        $gir_no = $stored_gir_no;
                    }else{
                        $gir_no = '[]';
                    }
                }else{
                    $gir_no = json_encode($multi_files);
                }
            }            

            if($val == 'plot_doc'){
                $stored_plot_doc = $loan_document['common_data']['stored_plot_doc'] ?? null;
                if(count($multi_files) == 0){
                    if(!empty($stored_plot_doc)){
                        $plot_doc = $stored_plot_doc;
                    }else{
                        $plot_doc = '[]';
                    }
                }else{
                    $plot_doc = json_encode($multi_files);
                }
            }

            if($val == 'land_doc'){
                $stored_land_doc = $loan_document['common_data']['stored_land_doc'] ?? null;
                if(count($multi_files) == 0){
                    if(!empty($stored_land_doc)){
                        $land_doc = $stored_land_doc;
                    }else{
                        $land_doc = '[]';
                    }
                }else{
                    $land_doc = json_encode($multi_files);
                }
            }

            if($val == 'income_proof'){
                $stored_income_proof = $loan_document['common_data']['stored_income_proof'] ?? null;
                if(count($multi_files) == 0){
                    if(!empty($stored_income_proof)){
                        $income_proof = $stored_income_proof;
                    }else{
                        $income_proof = '[]';
                    }
                }else{
                    $income_proof = json_encode($multi_files);
                }
            }
        }
        if(isset($loan_document['common_data'])){
            LoanDocument::updateOrCreate([
                'home_loan_id' => !empty($homeLoan->id) ? $homeLoan->id : $edit_loanId,
            ],[
                'home_loan_id' => $homeLoan->id,
                'adhar_card' => $adhar_card,
                'gir_no' => $gir_no,
                'plot_doc' => $plot_doc,
                'land_doc' => $land_doc,
                'income_proof' => $income_proof
            ]);
        }
    }
}
