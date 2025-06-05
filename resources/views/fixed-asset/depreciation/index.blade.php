@php
$success = session()->pull('success');
$error = session()->pull('error');
$errorsList = session('errors'); // This is auto-flashed by Laravel
@endphp
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
                            <h2 class="content-header-title float-start mb-0">Depreciation</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                                    <li class="breadcrumb-item active">Depreciation List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
						<a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{route('finance.fixed-asset.depreciation.create')}}"><i data-feather="check-circle"></i> Process</a>
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
												<th>Document NO.</th>
												<th>Period</th>
												<th>Location</th>
												<th>Cost Center</th>
												<th>Dep. AMt</th>
												<th class="text-end">Status</th>
											  </tr>
											</thead>
											<tbody>
                        @foreach($data as $index=>$d)
                        @php $statusClasss =  $statusClasss = App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$d->document_status ?? App\Helpers\ConstantHelper::DRAFT]; @endphp
												 <tr>
													<td class="text-nowrap">{{$index+1}}</td>
													<td class="fw-bolder text-dark text-nowrap">{{ \Carbon\Carbon::parse($d->document_date)->format('d-m-Y') }}</td>
                      	<td class="text-nowrap">{{strtoupper($d?->book?->book_code)}}</td>
                          <td class="text-nowrap">{{$d->document_number}}</td>
													<td class="text-nowrap">{{\DateTime::createFromFormat('d-m-Y', explode(" to ", $d->period)[1])->format("M 'y")}}                          </td>
                          <td class="text-nowrap">{{$d?->Erplocation?->store_name??"-"}}</td>
													<td class="text-nowrap">{{$d?->cost_center?->name ?? "-"}}</td>
													<td class="text-nowrap">{{number_format($d->grand_total_dep_amount,2)}}</td>
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
                                      <a class="dropdown-item" href="{{route('finance.fixed-asset.depreciation.show', $d->id)}}">
                                        <i data-feather="edit" class="me-50"></i>
                                        <span>View Detail</span>
                                      </a>
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

    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
		<div class="modal-dialog sidebar-sm">
			<form class="add-new-record modal-content pt-0">
				<div class="modal-header mb-1">
					<h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
				</div>
				<div class="modal-body flex-grow-1">
					<div class="mb-1">
						  <label class="form-label" for="fp-range">Select Date</label>
<!--                        <input type="text" id="fp-default" class="form-control flatpickr-basic" placeholder="YYYY-MM-DD" />-->
						  <input type="text" id="fp-range" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
					</div>

					<div class="mb-1">
						<label class="form-label">Group</label>
						<select class="form-select select2">
							<option>Select</option>
						</select>
					</div>

                    <div class="mb-1">
						<label class="form-label">Company</label>
						<select class="form-select select2">
							<option>Select</option>
						</select>
					</div>

                    <div class="mb-1">
						<label class="form-label">Organization</label>
						<select class="form-select select2">
							<option>Select</option>
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
    }},

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

  // Add New record
  // ? Remove/Update this code as per your requirements ?
  var count = 101;
  $('.data-submit').on('click', function () {
    var $new_name = $('.add-new-record .dt-full-name').val(),
      $new_post = $('.add-new-record .dt-post').val(),
      $new_email = $('.add-new-record .dt-email').val(),
      $new_date = $('.add-new-record .dt-date').val(),
      $new_salary = $('.add-new-record .dt-salary').val();

    if ($new_name != '') {
      dt_basic.row
        .add({
          responsive_id: null,
          id: count,
          full_name: $new_name,
          post: $new_post,
          email: $new_email,
          start_date: $new_date,
          salary: '$' + $new_salary,
          status: 5
        })
        .draw();
      count++;
      $('.modal').modal('hide');
    }
  });

  // Delete Record
  $('.datatables-basic tbody').on('click', '.delete-record', function () {
    dt_basic.row($(this).parents('tr')).remove().draw();
  });



});

        $(".myrequesttablecbox tr").click(function() {
          $(this).addClass('trselected').siblings().removeClass('trselected');
          value = $(this).find('td:first').html();
        });

        $(document).on('keydown', function(e) {
          if (e.which == 38) {
            $('.trselected').prev('tr').addClass('trselected').siblings().removeClass('trselected');
          } else if (e.which == 40) {
            $('.trselected').next('tr').addClass('trselected').siblings().removeClass('trselected');
          }
          $('html, body').scrollTop($('.trselected').offset().top - 100);
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
            Toast.fire({
                icon,
                title
            });
        }

          @if ($success)
      showToast("success", "{{ $success }}");
  @endif

  @if ($error)
      showToast("error", "{{ $error }}");
  @endif

  @if ($errors->any())
      showToast('error',
          "@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach"
      );
  @endif






 handleRowSelection('.datatables-basic');



    </script>
@endsection
<!-- END: Body-->

@endsection
