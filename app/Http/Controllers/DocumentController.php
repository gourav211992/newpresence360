<?php

namespace App\Http\Controllers;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ErpDocument;
use Illuminate\Http\Request;
use App\Http\Requests\ErpDocumentRequest;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Str;
use App\Helpers\Helper;
use Auth;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;
        if ($request->ajax()) {
            $documents = ErpDocument::where('organization_id', $organizationId)
                ->orderBy('id', 'ASC')
                ->get();

            return DataTables::of($documents)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return '<span class="badge rounded-pill ' . ($row->status == 'active' ? 'badge-light-success' : 'badge-light-danger') . ' badgeborder-radius">
                                ' . ucfirst($row->status) . '
                            </span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('documents.edit', $row->id);
                    $deleteUrl = route('documents.destroy', $row->id);

                    return '<div class="dropdown">
                                <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                    <i data-feather="more-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="' . $editUrl . '">
                                       <i data-feather="edit-3" class="me-50"></i>
                                        <span>Edit</span>
                                    </a>
                                    <a href="#" class="dropdown-item text-danger delete-btn"
                                       data-url="' . $deleteUrl . '"
                                       data-message="Are you sure you want to delete this document?">
                                        <i data-feather="trash-2" class="me-50"></i>
                                        <span>Delete</span>
                                    </a>
                                </div>
                            </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('procurement.document.index');
    }

    public function create()
    {
        $status = ConstantHelper::STATUS;
        return view('procurement.document.create', compact('status'));
    }

    public function store(ErpDocumentRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validated = $request->validated();
        $validated['organization_id'] = $organization->id;
        $validated['group_id'] = $organization->group_id;
        $validated['company_id'] = $organization->company_id;
        $document = ErpDocument::create($validated);
        return response()->json([
            'status' => true,
            'message' => 'Record created successfully',
            'data' => $document,
        ]);
    }

    public function edit($id)
    {
        $document = ErpDocument::findOrFail($id);
        $status = ConstantHelper::STATUS;
        return view('procurement.document.edit', compact('document', 'status'));
    }

    public function update(ErpDocumentRequest $request, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validated = $request->validated();
        $validated['organization_id'] = $organization->id;
        $validated['group_id'] = $organization->group_id;
        $validated['company_id'] = $organization->company_id;
        $document = ErpDocument::findOrFail($id);
        $document->update($validated);
        return response()->json([
            'status' => true,
            'message' => 'Record updated successfully',
            'data' => $document,
        ]);

    }

    public function destroy($id)
    {
        try {
            $document = ErpDocument::findOrFail($id);
            $referenceCheck = $document->isReferenced();
            if (!$referenceCheck['status']) {
                return response()->json([
                    'status' => false,
                    'message' => 'Record cannot be deleted because it is already in use',
                ], 400);
            }
            $document->delete();
            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the record',
            ], 500);
        }
    }
}
