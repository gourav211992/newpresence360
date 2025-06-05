@extends('layouts.app')
@section('content')

    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Merger Asset</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>  
                                    <li class="breadcrumb-item active">Asset List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button> 
						<a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{ route('finance.fixed-asset.merger.create') }}"><i data-feather="plus-circle"></i> Add New</a> 
                    </div>
                </div>
            </div>
            <div class="content-body">
                 
                
				
				<section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
								
								   
                                <div class="table-responsive">
									<table class="datatables-basic table myrequesttablecbox tableistlastcolumnfixed"> 
                                            <thead>
                                             <tr>
												<th>#</th>
												<th>Date</th> 
												<th>Series</th>
												<th>Doc No.</th>
												<th>Asset Name</th>
												<th>Asset Code</th>
												<th>Cap. Date</th>
												<th>Qty</th>
											  <th>Ledger Name</th>
                        <th>Location</th>
                        <th>Cost Center</th>
												<th class="text-end">Status</th>
											  </tr>
											</thead>
											<tbody>
                                                @foreach($data as $key => $d)
                                                <tr>
													<td class="text-nowrap">{{$key+1}}</td>
													<td class="fw-bolder text-dark text-nowrap">{{ $d?->document_date ? \Carbon\Carbon::parse($d->document_date)->format('d-m-Y') : '' }}</td>
                          <td class="text-nowrap">{{$d?->book?->book_code}}</td>
													<td class="text-nowrap">{{$d?->document_number}}</td>
													<td class="text-nowrap">{{$d?->asset_name}}</td>
													<td class="text-nowrap">{{$d?->asset_code}}</td>
											    <td class="text-nowrap">{{ $d?->capitalize_date ? \Carbon\Carbon::parse($d->capitalize_date)->format('d-m-Y') : '' }}</td>
                          <td class="text-nowrap">{{ $d?->quantity }}</td>
                          <td class="text-nowrap">{{$d?->ledger?->name}}</td>
                          <td class="text-nowrap">{{ $d?->location?->store_name ??"-" }}</td>
                          <td class="text-nowrap">{{ $d?->cost_center?->name ??"-" }}</td>
                      <td class="tableactionnew">
                         <div class="d-flex align-items-center justify-content-end">
                           @php $statusClasss = App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$d->document_status??"draft"];  @endphp
                            <span
                                class='badge rounded-pill {{ $statusClasss }} badgeborder-radius'>
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
                                @if($d->document_status=="draft")
                                <a class="dropdown-item" href="{{ route('finance.fixed-asset.merger.edit', $d->id) }}">
																	<i data-feather="edit" class="me-50"></i>
																		<span>View</span>
																</a>
                                @else
                               
                                <a class="dropdown-item" href="{{ route('finance.fixed-asset.merger.show', $d->id) }}">
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

    <!-- BEGIN: Footer-->
    <!-- END: Footer-->
	
	 
    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
		<div class="modal-dialog sidebar-sm">
			<form class="add-new-record modal-content pt-0" method="POST"
      action="{{ route('finance.fixed-asset.merger.filter') }}" enctype="multipart/form-data">
     @csrf
				<div class="modal-header mb-1">
					<h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
				</div>
				<div class="modal-body flex-grow-1">
          <div class="mb-1">
              <label class="form-label" for="fp-range">Select Date</label>
              <input type="text" id="fp-range" name="date" value="{{ request('date') }}" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
          </div>
      
          <div class="mb-1">
              <label class="form-label">Asset Code</label>
              <select class="form-select" name="filter_asset">
                  <option value="">Select</option>
                  @foreach($assetCodes as $assetCode)
                      <option value="{{ $assetCode->id }}" {{ request('filter_asset') == $assetCode->id ? 'selected' : '' }}>
                          {{ $assetCode->asset_code }}
                      </option>
                  @endforeach
              </select>
          </div>
      
          <div class="mb-1">
              <label class="form-label">Ledger Name</label>
              <select class="form-select" name="filter_ledger">
                  <option value="">Select</option>
                  @foreach($ledgers as $ledger)
                      <option value="{{ $ledger->id }}" {{ request('filter_ledger') == $ledger->id ? 'selected' : '' }}>
                          {{ $ledger->name }}
                      </option>
                  @endforeach
              </select>
          </div>
      
          <div class="mb-1">
              <label class="form-label">Status</label>
              <select class="form-select" name="filter_status">
                  <option value="">Select</option>
                  @foreach (App\Helpers\ConstantHelper::DOCUMENT_STATUS as $key => $status)
                      <option value="{{ $status }}" {{ request('filter_status') == $status ? 'selected' : '' }}>
                          {{ ucfirst($status) }}
                      </option>
                  @endforeach
              </select>
          </div>
      </div>
         
			
				<div class="modal-footer justify-content-start">
					<button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
					<button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
				</div>
			</form>
		</div>
	</div>
@endsection
@section('scripts')
<script type="text/javascript" src="{{asset('assets/js/modules/finance-table.js')}}"></script>

    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })
		$(function () { 

  var dt_basic_table = $('.datatables-basic'),
    dt_date_table = $('.dt-date'),
    dt_complex_header_table = $('.dt-complex-header'),
    dt_row_grouping_table = $('.dt-row-grouping'),
    dt_multilingual_table = $('.dt-multilingual'),
    assetPath = '../../../app-assets/';

  if ($('body').attr('data-framework') === 'laravel') {
    assetPath = $('body').attr('data-asset-path');
  }

  // DataTable with buttons
  // --------------------------------------------------------------------

  if (dt_basic_table.length) {
    var dt_basic = dt_basic_table.DataTable({
      
      order: [[0, 'asc']],
      dom: 
        '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      scrollX: true,
        displayLength: 7,
      lengthMenu: [7, 10, 25, 50, 75, 100],
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-outline-secondary dropdown-toggle',
          text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + 'Export',
          buttons: [
            {
              extend: 'excel',
              text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
              className: 'dropdown-item',
               exportOptions: {
      columns: ':not(:last-child)' // exclude the last column
    }
            },
            
          ],
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
            $(node).parent().removeClass('btn-group');
            setTimeout(function () {
              $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
            }, 50);
          }
        },
         
      ],
      drawCallback: function() {
                    feather.replace();
                },
                
      
      language: {
        paginate: {
          // remove previous & next text from pagination
          previous: '&nbsp;',
          next: '&nbsp;'
        }
      }
    });
    $('div.head-label').html('<h6 class="mb-0">Event List</h6>');
  }

  // Flat Date picker
  if (dt_date_table.length) {
    dt_date_table.flatpickr({
      monthSelectorType: 'static',
      dateFormat: 'm/d/Y'
    });
  }

  

  // Delete Record
  $('.datatables-basic tbody').on('click', '.delete-record', function () {
    dt_basic.row($(this).parents('tr')).remove().draw();
  });
	
	 
 
});	
 handleRowSelection('.datatables-basic');
</script>
@endsection