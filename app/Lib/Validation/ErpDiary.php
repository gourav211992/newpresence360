<?php

namespace App\Lib\Validation;

use App\Helpers\ConstantHelper;
use App\Models\CRM\ErpDiary as CRMErpDiary;
use App\Models\ErpCustomer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class ErpDiary
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function store(): ValidationValidator
    {
        $erpCustomer = ErpCustomer::where('customer_code',$this->request->customer_code)->first();
        $validator = Validator::make(
            $this->request->all(),
            [
                "customer_type" => [
                    "required", Rule::in(ConstantHelper::CRM_CUSTOMER_TYPES)
                ],
                "customer_code" => [
                   "required"
                ],
                "contact_person" => [
                    $this->request->customer_type == "New" && !@$erpCustomer->contact_person ? "required" : "nullable", "string"
                ],
                "email_id" => [
                    "nullable", "string","email"
                ],
                "phone_no" => [
                    $this->request->customer_type == "New" && !@$erpCustomer->phone ? "required" : "nullable", "string",'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10', 'max:10',
                ],
                "location" => [
                    "nullable", "string"
                ],
                "subject" => [
                    "nullable", "string"
                ],
                "meeting_status_id" => [
                    $this->request->customer_type == "New" ? "required" : "nullable",
                ],
                "sales_representative_id" => [
                    $this->request->customer_type == "New" && !@$erpCustomer->sales_person_id ? "required" : "nullable",
                ],
                "currency_id" => [
                    $this->request->customer_type == "New" && !@$erpCustomer->currency_id ? "required" : "nullable",
                ],
                "sales_figure" => [
                    $this->request->customer_type == "New" && !@$erpCustomer->sales_figure ? "required" : "nullable",
                ],
                "industry_id" => [
                    $this->request->customer_type == "New" && !@$erpCustomer->industry_id? "required" : "nullable",
                ],
                "meeting_objective_id" => [
                    "required",
                ],
                "description" => [
                    "nullable", "string"
                ],
                "country_id" => [
                    "nullable", "numeric"
                ],
                "state_id" => [
                    "nullable", "numeric"
                ],
                "city_id" => [
                    "nullable", "numeric"
                ],
                "zip_code" => [
                    "nullable", "string"
                ],
                'attachment' => ['nullable','array'],
                'attachment.*' => ['mimes:jpeg,png,jpg,pdf,doc,docx,xlsx','max:2048'],
            ]
            ,[
                'customer_type.required' => "The customer type is required.",
                'sales_representative_id.required' => "The sales representative field is required.",
                'sales_figure.required' => "The sales figure field is required.",
                'customer_name.required' => "The customer name is required.",
                'customer_code.required' => "The customer name is required.",
                'contact_person.required' => "The contact person is required.",
                'currency_id.required' => "The currency is required.",
                'phone_no.required' => "The phone no is required.",
                'industry_id.required' => "The industry is required.",
                'meeting_status_id.required' => "The meeting status is required.",
                'meeting_objective_id.required' => "The meeting objective is required.",
                'attachment.mimes' => "The attachment field must be a file of type: jpeg, png, jpg, pdf, doc, docx.",
            ]
        );

        if ($validator->fails()) {
            return $validator;
        }

        $validator->after(function ($validator) {
        //    $cutomer = ErpCustomer::where('mobile',$this->request->phone_no)->first();
        //    if($cutomer && $this->request->customer_type == "New"){
        //         $validator->errors()->add('phone_no', 'The customer has already exist.');
        //    }

        //    if($this->request->email_id){
        //        $cutomerEmail = ErpCustomer::where('email',$this->request->email_id)->first();
        //        if($cutomerEmail && $this->request->customer_type == "New"){
        //             $validator->errors()->add('email_id', 'The email has already exist.');
        //        }
        //    }

           if($this->request->meeting_status_id){
               $diary = CRMErpDiary::where('meeting_status_id',$this->request->meeting_status_id)
               ->where('customer_code',$this->request->customer_code)
               ->first();
               if($diary){
                    $validator->errors()->add('meeting_status_id', 'You have already saved the diary with the same status.');
               }
           }
        });

        return $validator;
    }


    public function storeFeedback(): ValidationValidator
    {
        $validator = Validator::make(
            $this->request->all(),
            [
                'organization_id' => ['required'],
                'customer_code' => ['required'],
                'organization_id' => ['required'],
                'feedback' => ['nullable','array'],
                'feedback.*' => ['nullable', 'string'],
            ]
            ,[
                'feedback.*.required' => "The field is required.",
            ]
        );

        return $validator;
    }

    public function leadContacts(): ValidationValidator
    {
        $validator = Validator::make(
            $this->request->all(),
            [
                "data" => [
                    "nullable",
                    "array"
                ],
                "data.*.contact_name" => [
                    "required",
                    "string",
                    "max:255",
                ],
                "data.*.contact_number" => [
                    "required",
                    "max:10",
                    "min:10",
                    "distinct",
                    "regex:/^([0-9\s\-\+\(\)]*)$/"
                ],
                "data.*.contact_email" => [
                    "required",
                    "email",
                    "max:255",
                    "distinct",
                ],
            ],[
                "data.*.contact_number.distinct" => "The contact number has a duplicate value.",
                "data.*.contact_email.distinct" => "The contact email has a duplicate value.",
                "data.*.contact_number.regex" => "The contact number format is invalid."
            ]
        );

        return $validator;
    }

}
