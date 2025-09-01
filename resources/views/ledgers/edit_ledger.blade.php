@extends('layouts.app')

@section('content')
<style>
        .middleinputerror {
        padding-bottom: 30px;
        }
        .middleinputerror span.text-danger {
            font-size: 12px;
            position: absolute;
            top: 38px;
        }
        .itemactive { position: absolute; left: 6px; font-size: 11px; top: 6px; color: #fff } 
        .iteminactive {  left: 24px; color: #999 } 
        .customernewsection-form .statusactiinactive .form-check-input { width: 80px; cursor: pointer}
        .customernewsection-form .statusactiinactive .form-check-input:checked + .itemactive { display: inline-block}
        .customernewsection-form .statusactiinactive .form-check-input:checked ~ .iteminactive { display: none }
        
        .customernewsection-form .statusactiinactive .form-check-input:not(:checked) + .itemactive { display: none}
        .customernewsection-form .statusactiinactive .form-check-input:not(:checked) ~ .iteminactive { display: inline-block }
    </style>
    

<!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

            <form id="formUpdate" action="{{ route('ledgers.update', $data->id) }}" enctype="multipart/form-data" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="updated_groups" id="updated_groups">
                <input type="hidden" name="ledger_code_type" value="{{ $data->ledger_code_type }}">
                <input type="hidden" name="prefix" value="{{$data->prefix}}" />
                <input type="hidden" name="actionType" id="actionType" value="submit"/>
               <div class="modal fade" id="amendConfirmPopup" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Amend Ledger
                </h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <input type="hidden" name="action_type" id="action_type_main">
            </div>
            <div class="modal-body pb-2">
                <div class="row mt-1">
                <div class="col-md-12">
                    <div class="mb-1">
                        <label class="form-label">Remarks</label>
                        <textarea name="amend_remarks" class="form-control cannot_disable"></textarea>
                    </div>
                    <div class = "row">
                        <div class = "col-md-8">
                            <div class="mb-1">
                                <label class="form-label">Upload Document</label>
                                <input name = "amend_attachments[]" onchange = "addFiles(this, 'amend_files_preview')" type="file" class="form-control cannot_disable" max_file_count = "2" multiple/>
                            </div>
                        </div>
                        <div class = "col-md-4" style = "margin-top:19px;">
                            <div class="row" id = "amend_files_preview">
                            </div>
                        </div>
                    </div>
                    <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">  
                <button type="button" class="btn btn-outline-secondary me-1" onclick="closeModal('amendConfirmPopup')">Cancel</button> 
                <button type="button" class="btn btn-primary" onclick = "submitAmend();">Submit</button>
            </div>
        </div>
    </div>
    </div>
                



                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 col-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Edit/View Ledger</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                            <li class="breadcrumb-item"><a href="{{ route('ledgers.index') }}">Ledger
                                                    List</a></li>
                                            <li class="breadcrumb-item active">View Ledger</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <a href="{{ route('ledgers.index') }}" class="btn btn-secondary btn-sm">
                                    <i data-feather="arrow-left-circle"></i> Back
                                </a>
                                 <button type="button" id="btnDelete" class="btn btn-danger d-none btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                                data-url="{{ route('ledgers.destroy', $data->id) }}" 
                                                data-redirect="{{ route('ledgers.index') }}"
                                                data-message="Are you sure you want to delete this record?">
                                                <i data-feather="trash-2" class="me-50"></i> Delete
                                            </button>
                               
                                @if(!isset(request()->revisionNumber))
                                    @if (isset($data))
                                       @if($buttons['delete'])
                                            <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                                data-url="{{ route('ledgers.destroy', $data->id) }}" 
                                                data-redirect="{{ route('ledgers.index') }}"
                                                data-message="Are you sure you want to delete this record?">
                                                <i data-feather="trash-2" class="me-50"></i> Delete
                                            </button>
                                        @endif
                                         @if ($buttons['submit'])
                                          <a href="javascript:void(0);"
                                    id="checkAndOpenModal" class="btn btn-primary btn-sm mb-50 mb-sm-0">
                                    <i data-feather="check-circle"></i> Submit
                                </a>
                                         @endif
                                       @if ($buttons['approve'])
                                        <a type="button" id="reject-button" data-bs-toggle="modal"
                                            data-bs-target="#approveModal" onclick = "setReject();"
                                            class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i
                                                data-feather="x-circle"></i> Reject</a>
                                        <a type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#approveModal" onclick = "setApproval();"><i
                                                data-feather="check-circle"></i> Approve</a>
                                    @endif
                                       @if ($buttons['amend'])
                                        <a type="button" data-bs-toggle="modal" id="btnAmend" data-bs-target="#amendmentconfirm"
                                            class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i>
                                            Amendment</a>
                                    @endif
                                    
                                    @endif
                            @endif
                             <a href="javascript:void(0);"
                                    id="btnSubmit" class="btn btn-primary btn-sm mb-50 mb-sm-0 d-none" >
                                    <i data-feather="check-circle"></i> Submit
                                </a>
                            
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-body">
                    <section id="basic-datatable">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body customernewsection-form">
                                        <div class="row">
                                            <div class="col-md-12">
                                            <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between"> 
                                                <div>
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                               
                                                <div>
                                                    <div class="d-flex align-items-center"> 
                                                        <div class="form-check form-check-primary form-switch statusactiinactive me-1">
                                                            <input type="checkbox" name="status" class="form-check-input" id="customSwitch3" {{$data->status==1?'checked':''}}>
                                                            <span class="itemactive">Active</span>
                                                            <span class="itemactive iteminactive">Inactive</span>
                                                        </div>
                                                    </div>    
                                                </div>
                                            </div>
                                        </div> 

                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Group <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" multiple id="ledger_group_id"
                                                            name="ledger_group_id[]" required>
                                                            @foreach ($groups as $group)
                                                                <option value="{{ $group->id }}"
                                                                    data-ledgergroup="{{ $group->parent_group_id }}"
                                                                    {{ in_array($group->id, old('ledger_group_id', json_decode($data->ledger_group_id, true) ?? [])) ? 'selected' : '' }}>
                                                                    {{ $group->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div hidden class="col-md-3">
                                                        <a href="{{ route('ledger-groups.create') }}"
                                                            class="voucehrinvocetxt mt-0">Add Group</a>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Ledger Code <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="code" class="form-control" required
                                                            value="{{ old('code', $data->code) }}" />
                                                        @error('code')
                                                            <span class="alert alert-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Ledger Name <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="name" class="form-control" required
                                                            value="{{ old('name', $data->name) }}" />
                                                        @error('name')
                                                            <span class="alert alert-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                

                                                <div hidden class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Cost Center</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select name="cost_center_id" class="form-select select2">
                                                            <option value="">Select</option>
                                                            @foreach ($costCenters as $costCenter)
                                                                <option value="{{ $costCenter->id }}"
                                                                    {{ old('cost_center_id', $data->cost_center_id) == $costCenter->id ? 'selected' : '' }}>
                                                                    {{ $costCenter->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            @include('partials.approval-history', ['document_status' =>$data->document_status, 'revision_number' => $data->revision_number])
                                                
                                                </div>

                                          
                                        </div>

                                        <div class="mt-2" id="gst" style="display: none">
                                            <div class="step-custhomapp bg-light">
                                                <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-bs-toggle="tab"
                                                            href="#UOM">Applicability</a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="tab-content pb-1 px-1">
                                                <div class="tab-pane active" id="UOM">
                                                    <div class="row align-items-center mb-1" id="tax_type_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label">Tax Type <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <select class="form-select" id="tax_type" name="tax_type">
                                                                <option value="">Select</option>
                                                                @foreach (App\Helpers\ConstantHelper::getTaxTypes() as $value => $label)
                                                                    <option value="{{ $value }}"
                                                                        {{ $data->tax_type == $value ? 'selected' : '' }}>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1" id="tax_percentage_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label">% Calculation <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tax_percentage" name="tax_percentage"
                                                                value="{{ $data->tax_percentage }}" step="0.01"
                                                            pattern="^\d+(\.\d{1,2})?$" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1" id="tds_section_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label">TDS Section Type<span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <select class="form-select select2" name="tds_section"
                                                                id="tds_section">
                                                                <option value="">Select</option>
                                                                @foreach (App\Helpers\ConstantHelper::getTdsSections() as $value => $label)
                                                                    <option value="{{ $value }}"
                                                                        {{ $data->tds_section == $value ? 'selected' : '' }}>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1" id="tds_percentage_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label">% TDS With PAN <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tds_percentage" name="tds_percentage"
                                                                value="{{ $data->tds_percentage }}"  step="0.01"
                                                            pattern="^\d+(\.\d{1,2})?$"/>
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1" id="tds_percentage_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label"> % TDS Without PAN <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tds_without_pan" name="tds_without_pan" value="{{ $data->tds_without_pan }}" step="0.01"
                                                            pattern="^\d+(\.\d{1,2})?$" />
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1" id="tcs_section_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label">TCS Section Type<span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <select class="form-select select2" name="tcs_section"
                                                                id="tcs_section">
                                                                <option value="">Select</option>
                                                                @foreach (App\Helpers\ConstantHelper::getTcsSections() as $value => $label)
                                                                    <option value="{{ $value }}"
                                                                        {{ $data->tcs_section == $value ? 'selected' : '' }}>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1" id="tds_capping_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label"> TDS Capping <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tds_capping" name="tds_capping" step="any" value="{{ $data->tds_capping }}" />
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1" id="tcs_percentage_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label">% TCS With PAN <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tcs_percentage" name="tcs_percentage"
                                                                value="{{ $data->tcs_percentage }}" step="0.01"
                                                            pattern="^\d+(\.\d{1,2})?$" />
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1" id="tcs_without_pan_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label"> % TCS Without PAN <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tcs_without_pan" name="tcs_without_pan" value="{{ $data->tcs_without_pan }}" step="0.01"
                                                            pattern="^\d+(\.\d{1,2})?$" />
                                                        </div>
                                                    </div>
                                                   
                                                    <div class="row align-items-center mb-1" id="tcs_capping_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label"> TCS Capping <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tcs_capping" name="tcs_capping"  step="any" value="{{ $data->tcs_capping }}" />
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
          

            </form>
        </div>
    </div>



    <!-- Modal for group updates -->
    <div class="modal fade text-start" id="postvoucher" tabindex="-1" aria-labelledby="myModalLabel17"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Update
                            Remove Group to New Group</h4>
                        <p class="mb-0">For all the Submitted Voucher</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table
                                    class="mt-1 table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Remove Group</th>
                                            <th>New Group</th>
                                        </tr>
                                    </thead>
                                    <tbody id="group-table">
                                        @foreach ($groupsModal as $index => $group)
                                            @isset($group->id)
                                                <tr id="{{ $index }}">
                                                    <input type="hidden" name="removeGroup[]" value="{{ $group->id }}">
                                                    <input type="hidden" name="removeGroupName[]"
                                                        value="{{ $group->name }}">
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $group->name }}</td>
                                                    <td>
                                                        <select disabled class="form-select group-select mw-100"
                                                            data-index="{{ $index }}" name="updatedGroup[]">
                                                            <option value="">Select Group</option>
                                                            @foreach ($groups as $grp)
                                                                <option value="{{ $grp->id }}">{{ $grp->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                            @endisset
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i data-feather="x-circle"></i> Cancel
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="SubmitUpdate()">
                        <i data-feather="check-circle"></i> Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form id="approveLedgerForm" method="POST" action="{{ route('approveLedger') }}" data-redirect="{{ route('ledgers.index') }}" enctype='multipart/form-data'>
          @csrf

          <input type="hidden" class = "cannot_disable" name="action_type" id="action_type">
          <input type="hidden" class = "cannot_disable" name="id" value="{{isset($data) ? $data -> id : ''}}">
         <div class="modal-header">
            <div>
               <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="approve_reject_heading_label">
               </h4>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body pb-2">
            <div class="row mt-1">
               <div class="col-md-12">
                  <div class="mb-1">
                     <label class="form-label">Remarks</label>
                     <textarea name="remarks" class="form-control cannot_disable"></textarea>
                  </div>
                  <div class="row">
                    <div class = "col-md-8">
                        <div class="mb-1">
                            <label class="form-label">Upload Document</label>
                            <input type="file" name = "attachment[]" multiple class="form-control cannot_disable" onchange = "addFiles(this, 'approval_files_preview');" max_file_count = "2"/>
                        </div>
                    </div>
                    <div class = "col-md-4" style = "margin-top:19px;">
                        <div class = "row" id = "approval_files_preview">

                        </div>
                    </div>
                  </div>
                  <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                  
               </div>
            </div>
         </div>
         <div class="modal-footer justify-content-center">  
            <button type="reset" class="btn btn-outline-secondary me-1" onclick="closeModal('approveModal')">Cancel</button> 
            <button type="submit" class="btn btn-primary">Submit</button>
         </div>
       </form>
      </div>
   </div>
</div>
  <!-- END: Content-->
    <div class="modal fade" id="approveModal2" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form class="ajax-input-form" method="POST" action="{{ route('approveLedger') }}"
                    data-redirect="{{ route('ledgers.index') }}" enctype='multipart/form-data'>
                    @csrf
                    <input type="hidden" name="action_type" id="action_type">
                    <input type="hidden" name="id" value="{{ $data->id }}">
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17"></h4>
                            <p class="mb-0 fw-bold voucehrinvocetxt mt-0">{{ Carbon\Carbon::now()->format('d-m-Y') }}
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pb-2">
                        <div class="row mt-1">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                    <textarea name="remarks" class="form-control"></textarea>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" multiple class="form-control" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submit-button">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Amendment Modal --}}
    <div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1"
        aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>Ledger</strong>? After Amendment
                        this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" onclick="submitamend()" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>    <!-- END: Content-->
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('assets/js/preventkey.js') }}"></script>
    <script>
        const existingLedgers = @json($existingLedgers);
        const ExistingTdsSections = @json($ExistingTdsSections);
        const ExistingTcsSections = @json($ExistingTcsSections);
        $(document).ready(function() {
            $('#amendConfirm').hide();
            $('#checkAndOpenModal').on('click', function() {
                const currentCode = $('input[name="code"]').val()?.trim().toLowerCase();
                const currentName = $('input[name="name"]').val()?.trim().toLowerCase();

                const originalCode = $('input[name="code"]').attr('value')?.trim().toLowerCase();
                const originalName = $('input[name="name"]').attr('value')?.trim().toLowerCase();
                $('.preloader').show();
                if (currentCode !== originalCode) {
                    if (existingLedgers.some(l => l.code.toLowerCase() === currentCode)) {
                        $('.preloader').hide();
                        showToast('error', 'Ledger code already exists.', 'Duplicate Entry');
                        return;
                    }
                }

                if (currentName !== originalName) {
                    if (existingLedgers.some(l => l.name.toLowerCase() === currentName)) {
                        $('.preloader').hide();
                        showToast('error', 'Ledger name already exists.', 'Duplicate Entry');
                        return;
                    }
                }

                let selectedGroups = $('#ledger_group_id').val() || [];
                let selectedTdsSection = $('#tds_section').val();
                let selectedTcsSection = $('#tcs_section').val();
                let originalTdsSection = $('#tds_section').attr('value'); // Original TDS section value
                
                if (selectedTdsSection && selectedGroups.length > 0 && selectedTdsSection !== originalTdsSection) {
                    // Check if any of the selected groups have TDS in their name (indicating TDS group)
                    let hasTdsGroup = false;
                    selectedGroups.forEach(groupId => {
                        let groupOption = $('#ledger_group_id option[value="' + groupId + '"]');
                        if (groupOption.text().toLowerCase().includes('tds')) {
                            hasTdsGroup = true;
                        }
                    });
                    
                    if (hasTdsGroup) {
                        // Check if TDS section already exists in any of the selected groups (excluding current record)
                        let duplicateTdsSection = ExistingTdsSections.some(existing => {
                            return existing.tds_section === selectedTdsSection && 
                                   existing.ledger_group_ids.some(existingGroupId => 
                                       selectedGroups.includes(existingGroupId.toString())
                                   );
                        });
                        
                        if (duplicateTdsSection) {
                            $('.preloader').hide();
                            showToast('error', 'This TDS section type already exists in the selected TDS group.', 'Duplicate TDS Section');
                            return;
                        }
                    }
                }

                if (selectedTcsSection && selectedGroups.length > 0 && selectedTcsSection !== originalTcsSection) {
                    // Check if any of the selected groups have TDS in their name (indicating TDS group)
                    let hasTcsGroup = false;
                    selectedGroups.forEach(groupId => {
                        let groupOption = $('#ledger_group_id option[value="' + groupId + '"]');
                        if (groupOption.text().toLowerCase().includes('tcs')) {
                            hasTcsGroup = true;
                        }
                    });
                    
                    if (hasTcsGroup) {
                        // Check if TDS section already exists in any of the selected groups (excluding current record)
                        let duplicateTcsSection = ExistingTcsSections.some(existing => {
                            return existing.tcs_section === selectedTcsSection && 
                                   existing.ledger_group_ids.some(existingGroupId => 
                                       selectedGroups.includes(existingGroupId.toString())
                                   );
                        });
                        
                        if (duplicateTcsSection) {
                            $('.preloader').hide();
                            showToast('error', 'This TCS section type already exists in the selected TCS group.', 'Duplicate TCS Section');
                            return;
                        }
                    }
                }

                // Passed all checks, show modal
                const modal = new bootstrap.Modal(document.getElementById('postvoucher'));
                $('.preloader').hide();
                modal.show();
            });
            $('#btnSubmit').on('click', function() {
                const currentCode = $('input[name="code"]').val()?.trim().toLowerCase();
                const currentName = $('input[name="name"]').val()?.trim().toLowerCase();

                const originalCode = $('input[name="code"]').attr('value')?.trim().toLowerCase();
                const originalName = $('input[name="name"]').attr('value')?.trim().toLowerCase();
                $('.preloader').show();
                if (currentCode !== originalCode) {
                    if (existingLedgers.some(l => l.code.toLowerCase() === currentCode)) {
                        $('.preloader').hide();
                        showToast('error', 'Ledger code already exists.', 'Duplicate Entry');
                        return;
                    }
                }

                if (currentName !== originalName) {
                    if (existingLedgers.some(l => l.name.toLowerCase() === currentName)) {
                        $('.preloader').hide();
                        showToast('error', 'Ledger name already exists.', 'Duplicate Entry');
                        return;
                    }
                }

                // Passed all checks, show modal
                const modal = new bootstrap.Modal(document.getElementById('postvoucher'));
                $('.preloader').hide();
                modal.show();
            });


            let originalOptions = $('#ledger_group_id option').clone();
            $('#ledger_group_id').select2();
            $('#tds_section').select2();
            $('#tcs_section').select2();

            function toggleGstSection() {
                let selectedOptions = $('#ledger_group_id').val() || [];
                let showGst = false;

                // Hide all sections first
                $('#tax_type, #tax_percentage,#tax_type_label,#tax_percentage_label').attr('required', false)
            .hide();
                $('#tds_section, #tds_percentage,#tds_section_label, #tds_percentage_label,#tds_capping_label').attr('required', false)
                    .hide();
                $('#tcs_section, #tcs_percentage,#tcs_section_label, #tcs_percentage_label,#tcs_capping_label,#tcs_without_pan_label,#tcs_without_pan').attr('required', false)
                    .hide();

                // Check which special group is selected (only one can be selected)
                if ({{ $gst_group_id }} != null && selectedOptions.includes("{{ $gst_group_id }}")) {
                    showGst = true;
                    $('#tax_type, #tax_percentage,#tax_type_label,#tax_percentage_label').attr('required', true)
                        .show();
                } else if ({{ $tds_group_id }} != null && selectedOptions.includes("{{ $tds_group_id }}")) {
                    showGst = true;
                    $('#tds_section, #tds_percentage,#tds_section_label, #tds_percentage_label,#tds_capping_label').attr('required',
                        true).show();
                } else if ({{ $tcs_group_id }} != null && selectedOptions.includes("{{ $tcs_group_id }}")) {
                    showGst = true;
                    $('#tcs_section, #tcs_percentage,#tcs_section_label, #tcs_percentage_label,#tcs_capping_label,#tcs_without_pan_label,#tcs_without_pan').attr('required',
                        true).show();
                }

                // Toggle the GST section visibility
                if (showGst) {  
                    $('#gst').show();
                } else {
                    $('#gst').hide();
                }
            }

            function validateSpecialGroups(selectedOptions, newlySelected) {
                let gstSelected = {{ $gst_group_id }} != null && selectedOptions.includes("{{ $gst_group_id }}");
                let tdsSelected = {{ $tds_group_id }} != null && selectedOptions.includes("{{ $tds_group_id }}");
                let tcsSelected = {{ $tcs_group_id }} != null && selectedOptions.includes("{{ $tcs_group_id }}");

                // Count how many special groups are selected
                let specialGroupsSelected = [gstSelected, tdsSelected, tcsSelected].filter(Boolean).length;

                // Check if newly selected option is a special group
                let isNewlySelectedSpecial = (
                    newlySelected == "{{ $gst_group_id }}" ||
                    newlySelected == "{{ $tds_group_id }}" ||
                    newlySelected == "{{ $tcs_group_id }}"
                );

                // If trying to select more than one special group
                if (specialGroupsSelected > 1 && isNewlySelectedSpecial) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Selection',
                        text: 'You can only select one of GST, TDS or TCS groups at a time. Please deselect other groups first.',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }
                return true;
            }
            $('#ledger_group_id').on('change', function(e) {
                generateItemCode();
            });

            $('#ledger_group_id').on('select2:select', function(e) {
                generateItemCode();
                let selectedOptions = $(this).val();

                let newlySelected = e.params.data.id;

                // First validate the selection
                if (!validateSpecialGroups(selectedOptions, newlySelected)) {
                    // If invalid, remove the last selected option
                    let newOptions = selectedOptions.filter(option => option != newlySelected);
                    $(this).val(newOptions).trigger('change');

                    return;
                }

                // Toggle GST section based on selections
                toggleGstSection();

                // Handle parent-child relationship logic
                selectedOptions.forEach(function(selectedOption) {
                    let selectedOptionElement = $('#ledger_group_id option[value="' +
                        selectedOption + '"]');
                    let selectedLedgerGroupId = selectedOptionElement.attr('data-ledgergroup');

                    $("#ledger_group_id option").each(function() {
                        let optionValue = $(this).val();
                        let ledgerGroupId = $(this).data('ledgergroup');
                        if ((optionValue == selectedLedgerGroupId ||
                                selectedLedgerGroupId == ledgerGroupId) && !selectedOptions
                            .includes(optionValue)) {
                            $(this).remove();
                        }
                    });
                });

                $(this).trigger('change.select2');
            });

            $('#ledger_group_id').on('select2:unselect', function(e) {
                generateItemCode();
                let selectedOptions = $(this).val() || [];

                // Restore original options and re-select the remaining selections
                $('#ledger_group_id').html(originalOptions).trigger('change.select2');
                selectedOptions.forEach(function(value) {
                    $('#ledger_group_id option[value="' + value + '"]').prop('selected', true);
                });

                // Toggle GST section based on remaining selections
                toggleGstSection();

                // Handle parent-child relationship logic
                selectedOptions.forEach(function(selectedOption) {
                    let selectedOptionElement = $('#ledger_group_id option[value="' +
                        selectedOption + '"]');
                    let selectedLedgerGroupId = selectedOptionElement.attr('data-ledgergroup');

                    $("#ledger_group_id option").each(function() {
                        let optionValue = $(this).val();
                        let ledgerGroupId = $(this).data('ledgergroup');
                        if ((optionValue == selectedLedgerGroupId ||
                                selectedLedgerGroupId == ledgerGroupId) &&
                            selectedOptionElement.val() != optionValue &&
                            !selectedOptions.includes(optionValue)) {
                            $(this).remove();
                        }
                    });
                });
            });

            // Initialize the view on page load
            toggleGstSection();
        });

        function updateTableDropdowns() {
            let selectedValues = $('#ledger_group_id').val() || [];
            let $tableBody = $('#group-table');
            let existingRows = $tableBody.find('tr').length;
            let groups = Object.values({!! json_encode($groups) !!});

            let removeGroupValues = $('input[name="removeGroup[]"]').map(function() {
                return $(this).val();
            }).get();

            // Add rows if selected values exceed current rows
            while (selectedValues.length > existingRows) {
                let newRowIndex = existingRows;
                let newRow = `
            <tr>
                <input type="hidden" name="removeGroup[]" value="0">
                <input type="hidden" name="removeGroupName[]" value="0">
                <td>${newRowIndex + 1}</td>
                <td>New Group</td>
                <td>
                    <select disabled class="form-select group-select mw-100" data-index="${newRowIndex}" name="updatedGroup[]">
                        <option value="">Select Group</option>
                        ${groups.map(grp => `<option value="${grp.id}">${grp.name}</option>`).join('')}
                    </select>
                </td>
            </tr>
            `;
                $tableBody.append(newRow);
                existingRows++;
            }

            let assignedGroups = [];
            $tableBody.find('tr').each(function(index) {
                let row = $(this);
                let removeGroupValue = row.find('input[name="removeGroup[]"]').val();
                let updatedGroupDropdown = row.find('select[name="updatedGroup[]"]');

                if (selectedValues.includes(removeGroupValue)) {
                    updatedGroupDropdown.val(removeGroupValue);
                    assignedGroups.push(removeGroupValue);
                }
            });

            // Assign remaining values to unfilled rows
            $tableBody.find('tr').each(function() {
                let row = $(this);
                let updatedGroupDropdown = row.find('select[name="updatedGroup[]"]');

                if (!updatedGroupDropdown.val()) {
                    let remainingValue = selectedValues.find(value => !assignedGroups.includes(value));
                    if (remainingValue) {
                        updatedGroupDropdown.val(remainingValue);
                        assignedGroups.push(remainingValue);
                    }
                }
            });
        }

        $('#postvoucher').on('shown.bs.modal', function() {
            updateTableDropdowns();
        });

        function SubmitUpdate() {
            let groupsData = [];
                    let updatedGroupValues = new Set();
                    let hasDuplicate = false;
               if($('#actionType').val()=="amendment"){
                    $('#group-table tr').each(function() {
                        let removeGroup = $(this).find('input[name="removeGroup[]"]').val();
                        let removeGroupName = $(this).find('input[name="removeGroupName[]"]').val();
                        let updatedGroup = $(this).find('select[name="updatedGroup[]"]').val();

                        if (updatedGroupValues.has(updatedGroup)) {
                            hasDuplicate = true;
                        } else {
                            updatedGroupValues.add(updatedGroup);
                        }

                        groupsData.push({
                            removeGroup: removeGroup,
                            removeGroupName: removeGroupName,
                            updatedGroup: updatedGroup
                        });
                    });

                    if (hasDuplicate) {
                       // $('.preloader').hide();
                        showToast('error', 'Duplicate updated groups are not allowed!');
                        return;
                    }



                    $('#updated_groups').val(JSON.stringify(groupsData));
                    openAmendConfirmModal();
                }
                    else{
            
            Swal.fire({
                title: "Are you sure?",
                text: "This change will reflect on all your voucher entry with same group and updated in all report",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, update it!"
            }).then((result) => {
                if (result.isConfirmed) {
                   $('#group-table tr').each(function() {
                        let removeGroup = $(this).find('input[name="removeGroup[]"]').val();
                        let removeGroupName = $(this).find('input[name="removeGroupName[]"]').val();
                        let updatedGroup = $(this).find('select[name="updatedGroup[]"]').val();

                        if (updatedGroupValues.has(updatedGroup)) {
                            hasDuplicate = true;
                        } else {
                            updatedGroupValues.add(updatedGroup);
                        }

                        groupsData.push({
                            removeGroup: removeGroup,
                            removeGroupName: removeGroupName,
                            updatedGroup: updatedGroup
                        });
                    });

                    if (hasDuplicate) {
                       // $('.preloader').hide();
                        showToast('error', 'Duplicate updated groups are not allowed!');
                        return;
                    }



                    $('#updated_groups').val(JSON.stringify(groupsData));
                    $('.preloader').show();
                    $('#formUpdate').submit();
            
                }
            });
        }
        }

        function showToast(type, message, title) {
            Swal.fire({
                icon: type,
                text: message,
                title: title,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'OK'
            });
        }
        const itemInitialInput = $('input[name="prefix"]');
        const itemCodeType = '{{ $data->ledger_code_type }}';
        console.log(itemCodeType, "ITEM TYPE");
        const itemCodeInput = $('input[name="code"]');
        if (itemCodeType === 'Manual') {
            itemCodeInput.prop('readonly', false);
        } else {
            itemCodeInput.prop('readonly', true);
        }


        function generateItemCode() {
            const selectedData = $('#ledger_group_id').select2('data');
            const itemName = selectedData.length > 0 ? selectedData[0].text : "";
            const groupId = selectedData.length > 0 ? $('#ledger_group_id').val()[0] : "";
            if (itemCodeType === 'Manual') {
                return;
            }
            $.ajax({
                url: '{{ route('generate-ledger-code') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    group_id: groupId,
                    ledger_id: '{{ $data->id }}',

                },
                success: function(response) {
                    itemCodeInput.val((response.code || ''));
                    itemInitialInput.val(response.prefix || '');

                },
                error: function() {
                    itemCodeInput.val('');
                    itemInitialInput.val('')
                }
            });
        }
        if (itemCodeType === 'Auto') {

            generateItemCode();
        }
    function setApproval()
    {
        document.getElementById('action_type').value = "approve";
        document.getElementById('approve_reject_heading_label').textContent = "Approve Ledger";

    }
    function setReject()
    {
        document.getElementById('action_type').value = "reject";
        document.getElementById('approve_reject_heading_label').textContent = "Reject Ledger";
    }
         $(document).on('click', '#amendmentSubmit', (e) => {
            let actionUrl = "{{ route('ledgers.amendment', $data->id) }}";
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error'
                        });
                    }
                    location.reload();
                });
            });
        });
        @if(!$buttons['submit'])
        $('#formUpdate').find('input, select').prop('disabled', true);
        $('#revisionNumber').prop('disabled', false);
        @endif

         $(function() {
            $("#revisionNumber").change(function() {
                const fullUrl = "{{ route('ledgers.edit', $data->id) }}?revisionNumber=" +
                    $(this)
                    .val();
                window.open(fullUrl, "_blank");
            });
        });
       function submitamend(){
         $('#formUpdate').find('input, select').prop('disabled', false);
        $('#revisionNumber').prop('disabled', false);
        $('#btnSubmit').removeClass('d-none');
        $('#btnAmend').hide();
        $('#actionType').val('amendment');
        $('#amendmentconfirm').modal('hide');
        @if(App\Helpers\Helper::getAuthenticatedUser()->id == $data->created_by && $data->revision_number==0)
        $('#btnDelete').removeClass('d-none');
        @endif
        
       }
        function closeModal(id)
    {
        $('#' + id).modal('hide');
    }
    function openModal(id)
    {
        $('#' + id).modal('show');
    }
    

//File upload preview js code
    let fileInputData = {};
      const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    const MAX_FILE_SIZE = 5120; // in KB (5MB)
    function appendFilePreviews(fileUrl, previewElementId, index, fileId = null) {
    const previewContainer = document.getElementById(previewElementId);
    if (!previewContainer) return;

    const fileName = fileUrl.split('/').pop();

    const previewHtml = `
        <div class="col-1 file-preview-item" data-index="${index}" data-file-id="${fileId ?? ''}">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="feather feather-file-text me-2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>
        </div>
    `;

    previewContainer.insertAdjacentHTML('beforeend', previewHtml);
}



    function addFiles(element, previewElementId) {
        const input = element;
        const allowedMaxFilesCount = Number(element.getAttribute('max_file_count') ? element.getAttribute('max_file_count') : 1);
        const files = Array.from(input.files); // Convert new FileList to array
        const dt = new DataTransfer();
        const inputId = input.name.replace('[]','');
        // Initialize storage for this input if not already initialized
        if (!fileInputData[inputId]) {
            fileInputData[inputId] = [];
            addedFilesCount = 0;
        } else {
            addedFilesCount = fileInputData[inputId].length;
        }

        if ((files.length + fileInputData[inputId].length) > allowedMaxFilesCount) 
        {
            Swal.fire({
                title: 'Error!',
                text: "Maximum " + allowedMaxFilesCount + " files are allowed",
                icon: 'error',
            });
            let prevAllFiles = fileInputData[inputId] ? fileInputData[inputId] : [];
            let tempDt = new DataTransfer();
            prevAllFiles.forEach((fileElement) => {
                tempDt.items.add(fileElement);
            });
            input.files = tempDt.files;
            return;
        }

        // Combine old and new files
        let allFiles = [...fileInputData[inputId], ...files];
        var invalidFile = {};

        // Validate files
        for (let i = 0; i < allFiles.length; i++) {
            const file = allFiles[i];
            const fileExtension = file.name.split('.').pop().toLowerCase();

            if (!ALLOWED_EXTENSIONS.includes(fileExtension) || !ALLOWED_MIME_TYPES.includes(file.type)) {
                invalidFile.message = 'Please select valid files';
                break;
            }
            const fileSize = (file.size / 1024).toFixed(2);
            if (fileSize > MAX_FILE_SIZE) {
                invalidFile.message = 'Please select files with size not more than 5MB';
                break;
            }
        }

        // Stop if there's an invalid file
        if (invalidFile && invalidFile.message) {
            Swal.fire({
                title: 'Error!',
                text: invalidFile.message,
                icon: 'error',
            });
            element.value = ''; // Reset file input
            return;
        } else {
            // Add all files to DataTransfer and rebuild the preview
            allFiles.forEach((file, i) => {
                dt.items.add(file);
                if (!fileInputData[inputId].some(f => f.name === file.name && f.size === file.size)) {
                    const fileUrl = URL.createObjectURL(file);
                    appendFilePreviews(fileUrl, previewElementId, i);
                }
            });

            // Update the global object for this input
            fileInputData[inputId] = allFiles.reduce((unique, file) => {
                if (!unique.some(f => f.name === file.name && f.size === file.size)) {
                    unique.push(file);
                }
                return unique;
            }, []);

            // Update the file input's FileList
            input.files = dt.files;

            // Reset and re-render SVG icons (if applicable)
            feather.replace({
                width: 20,
                height: 20,
            });
        }
    }
    $('#approveLedgerForm').on('submit', function (e) {
        e.preventDefault();

        let form = this;
        let formData = new FormData(form);
        let actionUrl = $(form).attr('action');
        let redirectUrl = $(form).data('redirect');

        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    timer: 2000, // 2 seconds
                    text: response.message || 'Action completed successfully.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    }
                });
            },
            error: function (xhr) {
                let errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: errorMsg,
                    confirmButtonText: 'Close'
                });
            }
        });
    });
    function submitAmend()
    {$('.preloader').show();
         $("#amendConfirmPopup").modal('hide');
         $('#formUpdate').submit();
    }
     function openAmendConfirmModal()
    {
        $('#postvoucher').modal('hide');
        $("#amendConfirmPopup").modal("show");

    }

       


    
       
    </script>
@endsection
