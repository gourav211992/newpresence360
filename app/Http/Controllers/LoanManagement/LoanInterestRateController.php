<?php

namespace App\Http\Controllers\LoanManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InterestRate;
use App\Models\InterestRateScore;
use Carbon\Carbon;

class LoanInterestRateController extends Controller
{
    public function add(){
        $interest_rate = InterestRate::latest('created_at')->first();
        $base_rate = !empty($interest_rate->base_rate) ? $interest_rate->base_rate : '';
        return view('loan.interest_add', compact('base_rate'));
    }

    public function filteredRows($data){
        $filteredRows = [];
        if (isset($data['base_rate']) && is_array($data['base_rate'])) {
            $length = count($data['base_rate']);

            for ($index = 1; $index < $length; $index++) {
                $baseRate = $data['base_rate'][$index] ?? null;
                $cibilScoreMin = $data['cibil_score_min'][$index] ?? null;
                $cibilScoreMax = $data['cibil_score_max'][$index] ?? null;
                $riskCover = $data['risk_cover'][$index] ?? null;
                $interestRate = $data['interest_rate'][$index] ?? null;

                // Check if all fields in the row are empty
                if (!empty($baseRate) || !empty($cibilScoreMin) || !empty($cibilScoreMax) || !empty($riskCover) || !empty($interestRate)) {
                    $filteredRows[] = [
                        'cibil_score_min' => $cibilScoreMin,
                        'cibil_score_max' => $cibilScoreMax,
                        'risk_cover' => $riskCover,
                        'base_rate' => $baseRate,
                        'interest_rate' => $interestRate,
                    ];
                }
            }
        }

        return $filteredRows;
    }

    public function create(Request $request){

        $data = $request->all();
        $request->validate([
            'base_rate_val' => 'required',
            'effective_from' => 'required|date',
        ]);
        $filteredRows = $this->filteredRows($data);
        $latestInterestRate = InterestRate::latest('created_at')->first();
        $interestRate = InterestRate::create([
            'base_rate' => $request->base_rate_val,
            'effective_from' => $request->effective_from,
        ]);

        if ($interestRate) {
            foreach ($filteredRows as $row) {
                $interestRateValue = floatval(str_replace('%', '', $row['interest_rate']));
                InterestRateScore::create([
                    'base_rate' => $row['base_rate'],
                    'cibil_score_min' => $row['cibil_score_min'],
                    'cibil_score_max' => $row['cibil_score_max'],
                    'interest_rate' => $interestRateValue,
                    'interest_rate_id' => $interestRate->id,
                    'risk_cover' => $row['risk_cover'],
                ]);
            }
        }

        if($latestInterestRate){
            $effectiveFromDate = Carbon::parse($request->effective_from);
            $previousDayDate = $effectiveFromDate->subDay()->format('Y-m-d');
            $latestInterestRate->effective_to = $previousDayDate;
            $latestInterestRate->save();
        }

        return redirect("loan/interest-rate")->with('success', 'Interest rate created successfully');
    }

    public function edit($id)
    {
        $interest_rate = InterestRate::findOrFail($id);
        $interest_rate_score = InterestRateScore::where('interest_rate_id', $id)->get();
        $max_interest_rate_score = InterestRateScore::where('interest_rate_id', $id)->max('cibil_score_max');
        if($max_interest_rate_score){
            $score = $max_interest_rate_score;
        }else{
            $score = 0;
        }

        return view('loan.interest_edit', compact('interest_rate','interest_rate_score', 'score'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'base_rate_val' => 'required',
            'effective_from' => 'required|date'
        ]);
        $data = $request->all();
        $interest_rate = InterestRate::findOrFail($id);
        $interest_rate->effective_from = $data['effective_from'];
        $interest_rate->update();
        $filteredRows = $this->filteredRows($data);

        $interestRateScores = InterestRateScore::withTrashed()->where('interest_rate_id', $interest_rate->id)->get();  
        foreach ($interestRateScores as $score) {
            $score->forceDelete();
        }      
        if(count($filteredRows) > 0){
            foreach ($filteredRows as $row) {
                $interestRateValue = floatval(str_replace('%', '', $row['interest_rate']));
                InterestRateScore::create([
                    'base_rate' => $row['base_rate'],
                    'cibil_score_min' => $row['cibil_score_min'],
                    'cibil_score_max' => $row['cibil_score_max'],
                    'interest_rate' => $interestRateValue,
                    'interest_rate_id' => $id,
                    'risk_cover' => $row['risk_cover'],
                ]);
            }
        }

        return redirect("loan/interest-rate")->with('success', 'Interest rate updated successfully');
    }

    public function delete($id){

        $interest_rate = InterestRate::findOrFail($id);
        $interest_rate->delete();

        InterestRateScore::where('interest_rate_id', $id)->delete();

        return redirect("loan/interest-rate")->with('success', 'Interest rate deleted successfully');
    }
}
