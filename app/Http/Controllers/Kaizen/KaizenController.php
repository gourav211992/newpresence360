<?php

namespace App\Http\Controllers\Kaizen;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Kaizen\ErpKaizen;
use App\Models\Kaizen\ErpKaizenImprovement;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Lib\Validation\Kaizen\KaizenStoreRequest as Validator;
use App\Models\Kaizen\ErpKaizenDocument;
use App\Models\Kaizen\ErpKaizenTeam;
use Carbon\Carbon;
use PDF;

class KaizenController extends Controller
{
    public function index(Request $request){
        $user = Helper::getAuthenticatedUser();
        $length = $request->length ? $request->length : CommonHelper::PAGE_LENGTH_10;

        $kaizens = ErpKaizen::with([
                'kaizenTeam' => function($q){
                    $q->select('employees.id','name', 'email');
                },
                'approver' => function($q){
                    $q->select('employees.id','name', 'email');
                }
            ])
            ->where('organization_id',$user->organization_id)
            ->where(function($query) use($request){
                self::filter($request, $query);
            })
            ->paginate($length);
        return view('kaizen.index',[
            'kaizens' => $kaizens
        ]);
    }

    private function filter($request, $query){
        $today = Carbon::now();

        $startDate = $today->month >= 4 
            ? Carbon::create($today->year, 4, 1)->startOfDay()
            : Carbon::create($today->year - 1, 4, 1)->startOfDay();

        $endDate = $today; 

        // Check if there's an applied date filter
        if ($request->has('date_range') && $request->date_range != '') {
            $dates = explode(' to ', $request->date_range);
            $startDate = $dates[0] ? Carbon::parse($dates[0])->startOfDay() : null;
            $endDate = isset($dates[1]) ? Carbon::parse($dates[1])->startOfDay():  Carbon::parse($dates[0])->startOfDay();
        }

        if ($request->search) {
            $query->where(function($q) use($request){
                $q->where('kaizen_no', 'like', '%'.$request->search.'%');
            });
        }

        $query->whereBetween('kaizen_date', [$startDate, $endDate]);

        return $query;
    }

    public function create(){
        $user = Helper::getAuthenticatedUser();

        $departments = Department::where('organization_id',$user->organization_id)
            ->where('status',CommonHelper::ACTIVE)
            ->get();

        $improvements = ErpKaizenImprovement::where('organization_id',$user->organization_id)
            ->where('status',CommonHelper::ACTIVE)
            ->get()
            ->groupBy('type')
            ->map(function ($items) {
                return $items->pluck('description', 'id');
            })
            ->toArray();
        
        $kaizenNo = $this->generateKaizenNo($user);

        return view('kaizen.create',[
            'departments' => $departments,
            'improvements' => $improvements,
            'kaizenNo' => $kaizenNo,
        ]);
    }

    public function store(Request $request){
        $validator = (new Validator($request))->store();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $kaizenNo = $this->generateKaizenNo($user);

            $kaizen = new ErpKaizen();
            $kaizen->fill($request->all());

            $improvements = $request->improvement;
            $kaizen->group_id = $user->group_id;
            $kaizen->company_id = $user->company_id;
            $kaizen->organization_id = $user->organization_id;
            $kaizen->kaizen_no = $kaizenNo;
            $kaizen->kaizen_date = $request->date ?? null;
            $kaizen->productivity_imp_id = $improvements['productivity'] ?? null;
            $kaizen->quality_imp_id = $improvements['quality'] ?? null;
            $kaizen->moral_imp_id = $improvements['moral'] ?? null;
            $kaizen->delivery_imp_id = $improvements['delivery'] ?? null;
            $kaizen->cost_imp_id = $improvements['cost'] ?? null;
            $kaizen->innovation_imp_id = $improvements['innovation'] ?? null;
            $kaizen->cost_saving_amt = $improvements['cost_saving_amt'] ?? null;
            $kaizen->status = CommonHelper::PENDING;
            $kaizen->created_by = $user->id;
            $kaizen->save();

            if ($request->hasFile('after_kaizen')) {
                $attachments = $request->file('after_kaizen');
                foreach ($attachments as $key => $attachment) {
                    $documentName = uniqid('kaizen_') . '-' . $attachment->getClientOriginalName();
                    $attachment->move(public_path('attachments/kaizen'), $documentName);
                    $documentPath = 'attachments/kaizen/'.$documentName;

                    $kaizenAttachment = new ErpKaizenDocument();
                    $kaizenAttachment->type = CommonHelper::AFTER_KAIZEN;
                    $kaizenAttachment->kaizen_id = $kaizen->id;
                    $kaizenAttachment->attachment_path = $documentPath;
                    $kaizenAttachment->save();
                }
            }

            if ($request->hasFile('before_kaizen')) {
                $attachments = $request->file('before_kaizen');
                foreach ($attachments as $key => $attachment) {
                    $documentName = uniqid('kaizen_') . '-' . $attachment->getClientOriginalName();
                    $attachment->move(public_path('attachments/kaizen'), $documentName);
                    $documentPath = 'attachments/kaizen/'.$documentName;

                    $kaizenAttachment = new ErpKaizenDocument();
                    $kaizenAttachment->type = CommonHelper::BEFORE_KAIZEN;
                    $kaizenAttachment->kaizen_id = $kaizen->id;
                    $kaizenAttachment->attachment_path = $documentPath;
                    $kaizenAttachment->save();
                }
            }

            // ✅ Handle Team
            foreach($request->team_id as $teamId){
                $kaizenTeam = new ErpKaizenTeam();
                $kaizenTeam->kaizen_id = $kaizen->id;
                $kaizenTeam->team_id = $teamId ? $teamId : null;
                $kaizenTeam->save();
            }

            \DB::commit();
            return [
                "data" => null,
                "message" => "Kaizen created successfully!"
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
        
    }

    public function edit($id){
        $user = Helper::getAuthenticatedUser();
        $kaizen = ErpKaizen::with([
                'kaizenTeam' => function($q){
                    $q->select('employees.id','name', 'email');
                }
            ])->find($id);
        $attachments = ErpKaizenDocument::where('kaizen_id',$id)
            ->get()
            ->groupBy('type')
            ->map(function ($items) {
                return $items->pluck('attachment_path','id');
            })
            ->toArray();
            // dd($attachments);

        $departments = Department::where('organization_id',$user->organization_id)
            ->where('status',CommonHelper::ACTIVE)
            ->get();

        $improvements = ErpKaizenImprovement::where('organization_id',$user->organization_id)
            ->where('status',CommonHelper::ACTIVE)
            ->get()
            ->groupBy('type')
            ->map(function ($items) {
                return $items->pluck('description', 'id');
            })
            ->toArray();
        
        return view('kaizen.edit',[
            'departments' => $departments,
            'improvements' => $improvements,
            'kaizen' => $kaizen,
            'attachments' => $attachments,
        ]);
    }

    public function update(Request $request,$id){
        $request->merge(['id' => $id]);
        $validator = (new Validator($request))->update();
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        \DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();

            $kaizen = ErpKaizen::find($id);
            $kaizen->fill($request->all());

            $improvements = $request->improvement;
            $kaizen->group_id = $user->group_id;
            $kaizen->company_id = $user->company_id;
            $kaizen->organization_id = $user->organization_id;
            $kaizen->kaizen_date = $request->date ?? null;
            $kaizen->productivity_imp_id = $improvements['productivity'] ?? null;
            $kaizen->quality_imp_id = $improvements['quality'] ?? null;
            $kaizen->moral_imp_id = $improvements['moral'] ?? null;
            $kaizen->delivery_imp_id = $improvements['delivery'] ?? null;
            $kaizen->cost_imp_id = $improvements['cost'] ?? null;
            $kaizen->innovation_imp_id = $improvements['innovation'] ?? null;
            $kaizen->cost_saving_amt = $improvements['cost_saving_amt'] ?? null;
            $kaizen->status = CommonHelper::PENDING;
            $kaizen->save();

            if ($request->hasFile('after_kaizen')) {
                $attachments = $request->file('after_kaizen');
                foreach ($attachments as $key => $attachment) {
                    $documentName = uniqid('kaizen_') . '-' . $attachment->getClientOriginalName();
                    $attachment->move(public_path('attachments/kaizen'), $documentName);
                    $documentPath = 'attachments/kaizen/'.$documentName;

                    $kaizenAttachment = new ErpKaizenDocument();
                    $kaizenAttachment->type = CommonHelper::AFTER_KAIZEN;
                    $kaizenAttachment->kaizen_id = $kaizen->id;
                    $kaizenAttachment->attachment_path = $documentPath;
                    $kaizenAttachment->save();
                }
            }

            if ($request->hasFile('before_kaizen')) {
                $attachments = $request->file('before_kaizen');
                foreach ($attachments as $key => $attachment) {
                    $documentName = uniqid('kaizen_') . '-' . $attachment->getClientOriginalName();
                    $attachment->move(public_path('attachments/kaizen'), $documentName);
                    $documentPath = 'attachments/kaizen/'.$documentName;

                    $kaizenAttachment = new ErpKaizenDocument();
                    $kaizenAttachment->type = CommonHelper::BEFORE_KAIZEN;
                    $kaizenAttachment->kaizen_id = $kaizen->id;
                    $kaizenAttachment->attachment_path = $documentPath;
                    $kaizenAttachment->save();
                }
            }

            // ✅ Handle Team
            foreach($request->team_id as $teamId){
                ErpKaizenTeam::updateOrCreate(
                    ['kaizen_id' => $kaizen->id, 'team_id' => $teamId],
                    [] 
                );
            }

            \DB::commit();
            return [
                "data" => null,
                "message" => "Kaizen updated successfully."
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }
        
    }


    private function generateKaizenNo($user){
        $unitCode = $user->organization->unit_code ?? 'U00'; // default if null
        $today = now()->format('Ymd');

        // Find last kaizen no for today
        $lastKaizen = ErpKaizen::where('kaizen_no', 'like', "{$unitCode}%{$today}")
            ->orderBy('kaizen_no', 'desc')
            ->first();

        $lastIncrement = 0;
        if ($lastKaizen) {
            preg_match('/' . $unitCode . '(\d+)' . $today . '/', $lastKaizen->kaizen_no, $matches);
            $lastIncrement = isset($matches[1]) ? (int)$matches[1] : 0;
        }

        $nextIncrement = $lastIncrement + 1;
        $kaizenNo = "{$unitCode}-{$nextIncrement}{$today}";
        return $kaizenNo;

    }

    public function removeAttachment($id){
        $document = ErpKaizenDocument::find($id);

        if (!$document) {
            throw new ApiGenericException("Attachment not found.");
        }

        // Delete file from storage
        $fullPath = public_path($document->attachment_path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Delete DB record
        $document->delete();
        return [
            "data" => [
                'id' => $id
            ],
            "message" => "Attachment removed.",
            "status" => 200
        ];

    }

    public function destroy($id){
        $kaizen = ErpKaizen::find($id);
        if($kaizen->status == CommonHelper::APPROVED){
            throw new ApiGenericException("Cannot delete kaizen because it has already been approved.");
        }

        // Delete related teams
        $kaizen->teams()->delete();
        
        // Delete files and attachments
        foreach ($kaizen->attachments as $attachment) {
            $fullPath = public_path($attachment->attachment_path);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            $attachment->delete();
        }

        // Delete kaizen itself
        $kaizen->delete();

        return [
            "data" => [
                'id' => $id
            ],
            "message" => "Kaizen deleted successfully.",
            "status" => 200
        ];

    }

    public function pdfView($id){
        
        $kaizen = ErpKaizen::with([
                'kaizenTeam' => function($q){
                    $q->select('employees.id','name', 'email');
                },
                'department' => function($q){
                    $q->select('departments.id','name');
                },
                'createdBy' => function($q){
                    $q->select('id','name');
                },
                'approver' => function($q){
                    $q->select('id','name');
                }
            ])->find($id);
        // dd($kaizen);

        $attachments = ErpKaizenDocument::where('kaizen_id',$id)
            ->get()
            ->groupBy('type')
            ->map(function ($items) {
                return $items->pluck('attachment_path','id');
            })
            ->toArray();



        // return view('kaizen.pdf',[
        //     'kaizen' => $kaizen,
        //     'attachments' => $attachments,
        // ]);

        error_reporting(E_ALL ^ E_DEPRECATED);
        $pdf = \App::make('dompdf.wrapper');
        $pdf = PDF::loadView('kaizen.pdf', [
            'kaizen' => $kaizen,
            'attachments' => $attachments,
        ]);
        return $pdf->download('kaizen.pdf');

    }
}
