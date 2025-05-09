<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;

class AssessmentController extends Controller
{
    public function index(){
        return view('recruitment.assessment.index');
    }

    public function result(){
        return view('recruitment.assessment.index');
    }
}