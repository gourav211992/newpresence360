@extends('layouts.app')
@section('content')
   <!-- BEGIN: Content-->
   <div class="app-content content">
      <div class="content-overlay"></div>
      <div class="header-navbar-shadow"></div>
      <div class="content-wrapper container-xxl p-0">
         <div class="content-header row">
            <div class="content-header-left col-md-5 mb-2">
               <div class="row breadcrumbs-top">
                  <div class="col-12">
                     <h2 class="content-header-title float-start mb-0">Maintenance Work Orders</h2>
                     <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                           <li class="breadcrumb-item"><a href="{{route('/')}}">Home</a></li>  
                           <li class="breadcrumb-item active">Work Order List</li>
                        </ol>
                     </div>
                  </div>
               </div>
            </div>
            <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
               <div class="form-group breadcrumb-right">
                  <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal">
                     <i data-feather="filter"></i> Filter
                  </button> 
                  <a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{route('maint-wo.create')}}">
                     <i data-feather="plus-circle"></i> Add New
                  </a> 
               </div>
            </div>
         </div>

         <div class="content-body">
            <section id="basic-datatable">
               <div class="row">
                  <div class="col-12">
                     <div class="card">
                        <div class="table-responsive">
                           <table id="maint-wo-table" class="datatables-basic table myrequesttablecbox"> 
                              <thead>
                                 <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Series</th>
                                    <th>Doc No.</th>
                                    <th>Equipment</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th class="text-end">Status</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 @isset($data)
                                    @foreach($data as $d)
                                    
                                       <tr>
                                          <td class="text-nowrap">{{ $loop->iteration }}</td>
                                          <td class="fw-bolder text-dark text-nowrap">
                                             {{ \Carbon\Carbon::parse($d->document_date)->format('d-m-Y') ?? '-' }}
                                          </td>
                                          <td class="text-nowrap">{{ $d?->book?->book_code ?? '-' }}</td>
                                          <td class="text-nowrap">{{ $d->document_number ?? '-' }}</td>
                                          <td>{{ $d?->equipment_name ?? '-' }}</td>
                                          <td class="text-nowrap">{{ $d->equipment_category ?? '' }}</td>
                                          @php $maintenanceType = json_decode($d->equipment_details); @endphp
                                          <td class="text-nowrap">{{ $maintenanceType->equipment_maintenance_type_name ?? $maintenanceType->maintenance_type_name ?? '' }}</td>
                                          <td class="tableactionnew">
                                             <div class="d-flex align-items-center justify-content-end">
                                                @php 
                                                    $status = $d->document_status;
                                                    if (empty($status)) {
                                                        $status = "draft"; // fallback default
                                                    }
                                                    $statusClasss = App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$status];
                                                @endphp
                                                <span class="badge rounded-pill {{ $statusClasss }} badgeborder-radius">
                                                   @if ($d->document_status == App\Helpers\ConstantHelper::APPROVAL_NOT_REQUIRED)
                                                      Approved
                                                   @else
                                                      {{ ucfirst($d->document_status) }}
                                                   @endif
                                                </span>

                                                <div class="dropdown">
                                                   <button type="button" class="btn btn-sm dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown">
                                                      <i data-feather="more-vertical"></i>
                                                   </button>
                                                   <div class="dropdown-menu dropdown-menu-end">
                                                      @if ($d->document_status == 'draft' && $d->document_status!='closed')
                                                         <a class="dropdown-item" href="{{ route('maint-wo.edit', $d->id) }}">
                                                            <i data-feather="edit" class="me-50"></i>
                                                            <span>View</span>
                                                         </a>
                                                      @else
                                                         <a class="dropdown-item" href="{{ route('maint-wo.show', $d->id) }}">
                                                            <i data-feather="edit" class="me-50"></i>
                                                            <span>View</span>
                                                         </a>
                                                      @endif
                                                   </div>
                                                </div>
                                             </div>
                                          </td>
                                       </tr>
                                    @endforeach
                                 @endisset
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </section>
         </div>
      </div>
   </div>
   <!-- END: Content-->

   <div class="sidenav-overlay"></div>
   <div class="drag-target"></div>

   <!-- Filter Modal -->
   <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
      <div class="modal-dialog sidebar-sm">
         <form class="add-new-record modal-content pt-0"> 
            <div class="modal-header mb-1">
               <h5 class="modal-title">Apply Filter</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body flex-grow-1">
               <div class="mb-1">
                  <label class="form-label" for="fp-range">Select Date</label>
                  <input type="text" id="fp-range" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
               </div>
               <div class="mb-1">
                  <label class="form-label">Series</label>
                  <select class="form-select">
                     <option>Select</option>
                  </select>
               </div> 
               <div class="mb-1">
                  <label class="form-label">BOM Name</label>
                  <select class="form-select select2">
                     <option>Select</option> 
                  </select>
               </div>
               <div class="mb-1">
                  <label class="form-label">Status</label>
                  <select class="form-select">
                     <option>Select</option>
                     <option>Active</option>
                     <option>Inactive</option>
                  </select>
               </div> 
            </div>
            <div class="modal-footer justify-content-start">
               <button type="button" class="btn btn-primary data-submit mr-1">Apply</button>
               <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
         </form>
      </div>
   </div>
@endsection

@section('scripts')
<script type="text/javascript" src="{{ asset('assets/js/modules/finance-table.js') }}"></script>
<script>
   $(function() {
      // Initialize DataTable (client-side only, since data comes from controller)
      const dt = initializeBasicDataTable('.datatables-basic', 'Maintenance Work Orders');
      $('div.head-label').html('<h6 class="mb-0">Maintenance Work Orders</h6>');

      // Flatpickr for filter input
      $('#fp-range').flatpickr({
         mode: 'range',
         dateFormat: 'Y-m-d'
      });
   });

   function showToast(icon, title) {
      const Toast = Swal.mixin({
         toast: true,
         position: "top-end",
         showConfirmButton: false,
         timer: 3000,
         timerProgressBar: true,
         didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
         },
      });
      Toast.fire({ icon, title });
   }

   @if (session('success'))
      showToast("success", "{{ session('success') }}");
   @endif

   @if (session('error'))
      showToast("error", "{{ session('error') }}");
   @endif

   @if ($errors->any())
      showToast('error',
         "@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach"
      );
   @endif
</script>
@endsection
