@extends('layouts.app')

@section('content')
<!-- BEGIN: Content-->
 <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Budget</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">Add New</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right d-flex flex-wrap align-items-end justify-content-sm-end">
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0 me-50"><i data-feather="arrow-left-circle"></i> Back</button>
                            <button class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 me-50"><i data-feather='save'></i> Save as Draft</button>
                            <div class="dropdown me-50 mb-50 mb-sm-0">
                                <button type="button" class="btn btn-sm btn-warning dropdown-toggle exportcustomdrop" data-bs-toggle="dropdown">
                                    <i data-feather="share"></i> Export
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">
                                        <i data-feather="file" class="me-50"></i>
                                        <span>Excel</span>
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <i data-feather="clipboard" class="me-50"></i>
                                        <span>Pdf</span>
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <i data-feather="copy" class="me-50"></i>
                                        <span>Copy</span>
                                    </a>
                                </div>
                            </div>
                            <button form="budget-form" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Submit</button>
                        </div>
                    </div> -->
                </div>
            </div>
            <div class="content-body">


                <form id="budget-form" method="POST" action="{{ route('budget.update') }}">
                @csrf
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
                                                    </div>
                                                </div>


                                                <div class="col-md-6">

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Series <span class="text-danger">*</span></label>
                                                            </div>

                                                            <div class="col-md-7">

                                                               <select class="form-select" name="series" required readonly id="series">
                                                                    @foreach($series as $key => $serie)
                                                                        <option value="{{  $serie->id }}" @if($data->series == $serie->id ) selected @endif>{{ $serie->book_name }}</option>
                                                                    @endforeach
                                                                </select>

                                                                    @error('series')
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                            </div>
                                                         </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Document No. <span class="text-danger">*</span></label>
                                                            </div>

                                                            <div class="col-md-7">
                                                                <input type="text" class="form-control" name="documentno" id="documentno" value="{{ $data->documentno }}"  required readonly>
                                                                @error('document_no')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                         </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Budget Type <span class="text-danger">*</span></label>
                                                            </div>

                                                            <div class="col-md-8">
                                                                <div class="demo-inline-spacing">
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input type="radio" id="Generic" name="type" class="form-check-input" @if($data->type == 'Sale') checked="" @endif value="Sale">
                                                                        <label class="form-check-label fw-bolder" for="Generic">Sale</label>
                                                                    </div>
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input type="radio" id="Customer" name="type" class="form-check-input" @if($data->type == 'Purchase') checked="" @endif value="Purchase">
                                                                        <label class="form-check-label fw-bolder" for="Customer">Purchase</label>
                                                                    </div>
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input type="radio" id="Advanced" name="type" class="form-check-input" @if($data->type == 'Capital') checked="" @endif value="Capital">
                                                                        <label class="form-check-label fw-bolder" for="Advanced">Capital</label>
                                                                    </div>
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input type="radio" id="Expenses" name="type" class="form-check-input" @if($data->type == 'Expenses') checked="" @endif value="Expenses">
                                                                        <label class="form-check-label fw-bolder" for="Expenses">Expenses</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Business Unit <span class="text-danger">*</span></label>
                                                            </div>

                                                            <div class="col-md-7">
                                                                <input type="text" name="unit" required value="{{$data->unit}}" placeholder="Enter New or Select from previous" class="form-control ledgerselecct">
                                                            </div>
                                                         </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Company <span class="text-danger">*</span></label>
                                                            </div>

                                                            @php
                                                            $co = explode(',',$data->companies);
                                                            $br = explode(',',$data->branch);
                                                            $le = explode(',',$data->ledger);
                                                            @endphp
                                                            <div class="col-md-7">
                                                                <select class="form-select select2 companySelect" name="companies[]" multiple>
                                                                  <option disabled value="">Select Company
                                                                    </option>
                                                                    @foreach ($companies as $company)
                                                                        <option value="{{ $company->id }}" @if(in_array($company->id,$co)) selected @endif >
                                                                            {{ $company->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                         </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Branch/Unit <span class="text-danger">*</span></label>
                                                            </div>

                                                            <div class="col-md-7">
                                                                <select class="form-select select2" required name="branch[]" multiple>
                                                                    <option disabled value="">Select Branch
                                                                    </option>
                                                                    @foreach ($organ as $org)
                                                                        <option value="{{ $org->id }}" @if(in_array($org->id,$br)) selected @endif >
                                                                            {{ $org->name }}
                                                                        </option>
                                                                    @endforeach

                                                                </select>
                                                            </div>
                                                         </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Ledger</label>
                                                            </div>

                                                            <div class="col-md-7">
                                                                <select class="form-select select2" required name="ledger[]" multiple>
                                                                    <option disabled>Select</option>
                                                                  @foreach($ledgers as $leg)
                                                                    <option value="{{$leg->ledger_group_id}}" @if(in_array($leg->ledger_group_id,$le)) selected @endif>{{$leg->group ? $leg->group->name : 'N/A'}}</option>
                                                                  @endforeach
                                                                </select>
                                                            </div>
                                                         </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Target Budget</label>
                                                            </div>

                                                            <div class="col-md-7">
                                                                <input type="text" name="budget" value="{{$data->budget}}" class="form-control" />
                                                            </div>
                                                         </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Period <span class="text-danger">*</span></label>
                                                            </div>

                                                            <div class="col-md-7">
                                                                <input type="text" id="fp-range" required value="{{$data->period}}" name="period" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                                                            </div>
                                                         </div>



                                                </div>

                                                <div class="col-md-6">
                                                    <div class="actual-databudgetinfo">
                                                        <div class="card-header newheader">
                                                            <div class="header-left mt-1 mt-sm-0">
                                                                <h4 class="card-title">Budget Summary</h4>
                                                                <p class="card-text">Info Details</p>
                                                            </div>
                                                            <div class="header-right">
                                                                 <a href="budgetsumaryreport.html" class="btn btn-sm btn-outline-primary"><i data-feather="eye"></i> View Full Report</a>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 border-end ">
                                                                <div id="goal-overview-radial-bar-chart" class="my-2"></div>
                                                            </div>
                                                            <div class="col-md-6">
                                                            <div class="row text-center mx-0">
                                                                <div class="col-md-12 border-bottom py-1">
                                                                    <p class="card-text text-primary mb-0">Target Budget</p>
                                                                    <h3 class="fw-bolder text-primary  mb-0">186 Cr.</h3>
                                                                </div>
                                                                <div class="col-md-12 col-6 border-bottom py-1">
                                                                    <p class="card-text text-muted mb-0">Actual</p>
                                                                    <h3 class="fw-bolder mb-0">140 Cr.</h3>
                                                                </div>
                                                                <div class="col-md-12 col-6 py-1">
                                                                    <p class="card-text text-muted mb-0">Achieved</p>
                                                                    <h3 class="fw-bolder mb-0">83 %</h3>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>


                                              <div class="border-bottom mt-2 mb-2 pb-1 budget-head-stickypart">
                                                     <div class="row align-items-end">
                                                        <div class="col-md-2">
                                                            <div class="newheader">
                                                                <h4 class="card-title text-theme">Budget Detail</h4>
                                                                <p class="card-text">Fill the details</p>
                                                            </div>
                                                        </div>
                                                         <div class="col-md-10 budgetactionbtn">
                                                            <div class="d-flex flex-wrap align-items-center justify-content-sm-end">
                                                                    <div>
                                                                        <span class="badge rounded-pill badge-light-secondary forminnerstatus mb-50 mb-sm-0">
                                                                            Total % : <span class="text-info" id="level1-total">{{$data->total_percent}}</span>
                                                                        </span>
                                                                        <span class="badge rounded-pill badge-light-secondary forminnerstatus mb-50 mb-sm-0">
                                                                            Total Value (Cr.) : <span class="text-success" id="level1-value">{{$data->total_value}}</span>
                                                                        </span>
                                                                    </div>
                                                            </div>

                                                         </div>
                                                    </div>
                                             </div>




                                            <div class="row">




                                              <div class="col-md-12">


                                                     <div class="table-responsive">
                                                         <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad budgetbox-design">
                                                            <thead>
                                                                 <tr>
                                                                    <th width="30px">#</th>
                                                                    <th>Heads</th>
                                                                    <th width="70px">In %</th>
                                                                    <th width="70px">Value (Cr.)</th>
                                                                    <th width="70px">Actual (Cr.)</th>
                                                                    <th width="70px">Achieve %</th>
                                                                    <th width="100px">Action</th>
                                                                  </tr>
                                                                </thead>
                                                               <tbody class="budgetinfo">
    @php
        $jsonData = $data->details;
        $data = json_decode($jsonData, true);
    @endphp

    @foreach ($data as $entry)
        @php
            $level = $entry['level'];
            $headValue = $entry['head'] ?? '';
        @endphp

        @if (substr_count($level, '.') === 0)
            <tr class="categorybud" data-level="{{ $level }}" data-number="{{ $level }}">
                <td>{{ $level }}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="text" name="Heads[{{ $level }}][{{ $level }}]" value="{{ $headValue }}" class="form-control" />
                        <a href="#" class="budgetaddcatsub add-btn"><i data-feather="plus-circle"></i></a>
                        <a href="#" class="budgetaddcatsub remove-btn text-danger"><i data-feather="minus-circle"></i></a>
                    </div>
                </td>
                <td><input type="text" name="In[{{ $level }}][{{ $level }}]" readonly value="{{ $entry['in'] }}" class="form-control mw-100 input-in" /></td>
                <td><input type="text" name="Value[{{ $level }}][{{ $level }}]" readonly value="{{ $entry['value'] }}" class="form-control mw-100 text-end input-value" /></td>
                <td><input type="text" disabled="disabled" class="form-control mw-100 text-end" value="0.00" /></td>
                <td><input type="text" disabled="disabled" class="form-control mw-100" value="0" /></td>
                <td></td>
            </tr>
        @elseif (substr_count($level, '.') === 1)
            <tr class="sub-totol sub-categorybud" data-level="{{ $level }}" data-number="{{ $level }}">
                <td>{{ $level }}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="text" name="Heads[{{ $level }}][{{ $level }}]" value="{{ $headValue }}" class="form-control" />
                        <a href="#" class="budgetaddcatsub add-btn"><i data-feather="plus-circle"></i></a>
                        <a href="#" class="budgetaddcatsub remove-btn text-danger"><i data-feather="minus-circle"></i></a>
                    </div>
                </td>
                <td><input type="text" name="In[{{ $level }}][{{ $level }}]" readonly value="{{ $entry['in'] }}" class="form-control mw-100 input-in" /></td>
                <td><input type="text" name="Value[{{ $level }}][{{ $level }}]" readonly value="{{ $entry['value'] }}" class="form-control mw-100 text-end input-value" /></td>
                <td><input type="text" disabled="disabled" class="form-control mw-100 text-end" value="0.00" /></td>
                <td><input type="text" disabled="disabled" class="form-control mw-100" value="0" /></td>
                <td>
                    <!-- <button type="button" class="btn p-25 btn-sm btn-outline-secondary open-modal" style="font-size: 10px">Ledger</button> -->
                </td>
            </tr>
        @else
            <tr class="busnesheadbud" data-level="{{ $level }}" data-number="{{ $level }}">
                <td>&nbsp;</td>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="text" name="Heads[{{ $level }}][{{ $level }}]" value="{{ $headValue }}" class="form-control" />
                        <a href="#" class="budgetaddcatsub add-btn"><i data-feather="plus-circle"></i></a>
                        <a href="#" class="budgetaddcatsub text-danger"><i data-feather="minus-circle"></i></a>
                    </div>
                </td>
                <td><input type="text" name="In[{{ $level }}][{{ $level }}]" value="{{ $entry['in'] }}" class="form-control mw-100 input-in" /></td>
                <td><input type="text" name="Value[{{ $level }}][{{ $level }}]" value="{{ $entry['value'] }}" class="form-control mw-100 text-end input-value" /></td>
                <td><input type="text" disabled="disabled" class="form-control mw-100 text-end" value="0.00" /></td>
                <td><input type="text" disabled="disabled" class="form-control mw-100" value="0" /></td>
                <td>
                    <button type="button" class="btn p-25 btn-sm btn-outline-secondary open-modal" style="font-size: 10px">Ledger</button>
                </td>
            </tr>
        @endif
    @endforeach
</tbody>



                                                        </table>
                                                    </div>


                                                </div>


                                             </div>


                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal to add new record -->

                </section>
                </form>


                 <div class="modal fade" id="multiSelectModal" tabindex="-1" aria-labelledby="multiSelectModalLabel" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="multiSelectModalLabel">Select Options</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                              <div class="form-group">
                                <label for="options">Select ledger:</label>
                                 <select class="form-select select2" id="ledgerSelect" required name="ledger[]" multiple>
                                    <option disabled>Select</option>
                                  @foreach($ledgers as $leg)
                                    <option value="{{$leg->ledger_group_id}}">{{$leg->group ? $leg->group->name : 'N/A'}}</option>
                                  @endforeach
                                </select>
                              </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="saveSelection">Save changes</button>
                          </div>
                        </div>
                      </div>
                    </div>

            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection
@section('scripts')
<script>

    $(document).on('click', '.open-modal', function() {
        // Clear previous selection in Select2
        $('#ledgerSelect').val(null).trigger('change');

        // Open the modal programmatically
        $('#multiSelectModal').modal('show');
    });
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })
        $('#series').on('change', function() {
            var book_id = $(this).val();
            var request = $('#documentno');

            request.val(''); // Clear any existing options

            if (book_id) {
                $.ajax({
                    url: "{{ url('/budgets/get-request') }}/" + book_id,
                    type: "GET",
                    dataType: "json",
                    success: function(data)
                    {
                        console.log(data);
                            if (data.requestno) {
                            request.val(data.requestno);
                        }
                    }
                });
            }
        });


        $(function() {
            $( ".ledgerselecct" ).autocomplete({
                source: [ ],
                minLength: 0
            }).focus(function(){
                if (this.value == ""){
                    $(this).autocomplete("search");
                }
            });

            $( ".cgselecct" ).autocomplete({
                source: [
                    "Promotion",
                    "Distributor",
                    "Mis",
                ],
                    minLength: 0
                }).focus(function(){
                    if (this.value == ""){
                        $(this).autocomplete("search");
                    }
            });

            $( ".sbcgselecct" ).autocomplete({
                source: [
                    "Consumer Offer (For Footfalls & Conversion)",
                    "Consumer Experience (Footfall, Conversion, ASP, Attach Ratio)",
                    "Dealer Incentive",
                ],
                    minLength: 0
                }).focus(function(){
                    if (this.value == ""){
                        $(this).autocomplete("search");
                    }
            });

            $( ".bhselecct" ).autocomplete({
                source: [
                    "Consumer Offer",
                    "EMI Offers (BFL/HDFC)",
                    "Gift Voucher",
                    "Quess Program - Advisors in Bangalore, Pune & Committed at Distributor Stores",
                    "Dealer Champion Incentive on High End",
                    "BTL Budget to Cluster Head for Showrooms",
                ],
                    minLength: 0
                }).focus(function(){
                    if (this.value == ""){
                        $(this).autocomplete("search");
                    }
            });


          if (dt_date_table.length) {
            dt_date_table.flatpickr({
              monthSelectorType: 'static',
              dateFormat: 'm/d/Y'
            });
          }

        });

        $(".select2").select2({
            placeholder: "Select",
        });


        $(".mrntableselectexcel tr").click(function() {
          $(this).addClass('trselected').siblings().removeClass('trselected');
          value = $(this).find('td:first').html();
        });

        // $(document).on('keydown', function(e) {
        //   if (e.which == 38) {
        //     $('.trselected').prev('tr').addClass('trselected').siblings().removeClass('trselected');
        //   } else if (e.which == 40) {
        //     $('.trselected').next('tr').addClass('trselected').siblings().removeClass('trselected');
        //   }
        //    $('.mrntableselectexcel').scrollTop($('.trselected').offset().top - 40);
        // });

        $(document).on('change', '.companySelect', function () {
        var organizations = [];
        const id = $(this).attr('data-id');
        const company_id = $(this).val();
         var companies = {!! json_encode($companies) !!};

        $.each(companies, function (key, value)
        {
            if (value['id'] == company_id) {
                organizations = value['organizations'];
            }
        });

        $("#organization_id" + id).html("");
        $("#organization_id" + id).append("<option disabled value=''>Select Unit</option>");
        $.each(organizations, function (key, value) {
            $("#organization_id" + id).append("<option value='" + value['id'] + "'>" + value['name'] + "</option>");
        });
    });


    </script>
    <script type="text/javascript">
document.addEventListener('DOMContentLoaded', function () {
    let rowCount = 1; // Tracks the top-level categories

    function bindAddButtonEvents() {
        document.querySelectorAll('.add-btn').forEach(function (btn) {
            btn.removeEventListener('click', addRowHandler); // Remove existing listeners
            btn.addEventListener('click', addRowHandler); // Add new listener
        });
    }

    function bindInputEvents() {
    document.querySelectorAll('.input-in').forEach(function (input) {
            input.removeEventListener('input', calculateTotals); // Remove existing listeners
            input.addEventListener('input', calculateTotals); // Add new listener
        });
}

function bindInputValueEvents() {
   document.querySelectorAll('.input-value').forEach(function (input) {
            input.removeEventListener('input', calculateValueTotals); // Remove existing listeners
            input.addEventListener('input', calculateValueTotals); // Add new listener
        });
}


   function addRowHandler(e) {
    e.preventDefault();
    let row = this.closest('tr');
    let level = parseInt(row.getAttribute('data-level'));
    let currentNumber = row.getAttribute('data-number');
    let newRowHTML = '';

    if (level === 1) {
        // Add new main category
        rowCount++;
        newRowHTML += generateRow1(rowCount, 1); // Main row
        newRowHTML += generateRow11(rowCount + '.1', 2,rowCount); // Subcategory
        newRowHTML += generateRow111(rowCount + '.1.1', 3,rowCount); // Sub-subcategory
        insertAfterAllSeries(row, rowCount - 1, level, newRowHTML); // Pass newRowHTML to be inserted
    } else if (level === 2) {
        // Add new subcategory
        let parentNumber = currentNumber.split('.')[0]; // Get parent main number
        let lastSubNumber = 0; // Track the last sub number

        // Loop through existing rows to find the last subcategory number
        document.querySelectorAll('tr[data-level="2"]').forEach(function (subRow) {
            let subNumber = subRow.getAttribute('data-number');
            if (subNumber.startsWith(parentNumber + '.')) {
                lastSubNumber = Math.max(lastSubNumber, parseInt(subNumber.split('.')[1]));
            }
        });

        let newSubNumber = `${parentNumber}.${lastSubNumber + 1}`;
        newRowHTML += generateRow11(newSubNumber, 2,parentNumber); // Subcategory
        newRowHTML += generateRow111(newSubNumber + '.1', 3,parentNumber); // Sub-subcategory
        insertAfterAllSeries(row, parentNumber, level, newRowHTML); // Pass newRowHTML to be inserted
    } else if (level === 3) {
        // Add new sub-subcategory
        let parentSubNumber = currentNumber.split('.').slice(0, 2).join('.');
        let newSubSubNumber = parentSubNumber + '.' + (parseInt(currentNumber.split('.')[2]) + 1);
        newRowHTML += generateRow111(newSubSubNumber, 3,parentSubNumber.split('.')[0]); // Sub-subcategory
        row.insertAdjacentHTML('afterend', newRowHTML); // Insert immediately after current row
    }

    feather.replace(); // Reinitialize feather icons
    bindAddButtonEvents(); // Rebind event listeners for the newly added buttons
    bindInputEvents(); // Re-bind input events for all input fields, including newly added ones
    bindInputValueEvents();
}

// Initial binding on page load
bindAddButtonEvents();
bindInputEvents(); //
bindInputValueEvents();

// Function to calculate totals for all levels
function calculateTotals() {
   // Initialize an object to store sums for each level
    let levelSums = {};

    // Calculate totals for level 3 rows (sub-subcategories)
    document.querySelectorAll('tr[data-level="3"]').forEach(function (row) {
        let sum = 0;
        let inputs = row.querySelectorAll('.input-in');
        inputs.forEach(function (input) {
            sum += parseFloat(input.value) || 0; // Add the input value or 0 if NaN
        });

        // Get the parent row number (level 2)
        let parentRowNumber = row.getAttribute('data-number').split('.').slice(0, 2).join('.');
        levelSums[parentRowNumber] = (levelSums[parentRowNumber] || 0) + sum; // Accumulate sum for level 2
    });

    // Calculate totals for level 2 rows (subcategories)
    document.querySelectorAll('tr[data-level="2"]').forEach(function (row) {
        let sum = (levelSums[row.getAttribute('data-number')] || 0); // Start with accumulated sum from level 3
        let inputs = row.querySelectorAll('.input-in');
        inputs.forEach(function (input) {
            sum += parseFloat(input.value) || 0; // Add the input value or 0 if NaN
        });

        // Get the parent row number (level 1)
        let parentRowNumber = row.getAttribute('data-number').split('.')[0]; // Main category number
        levelSums[parentRowNumber] = (levelSums[parentRowNumber] || 0) + sum; // Accumulate sum for level 1
    });

    // Update the input values in the parent rows
    Object.keys(levelSums).forEach(function (parentNumber) {
        let parentRow = document.querySelector(`tr[data-number="${parentNumber}"]`);
        if (parentRow) {
            let parentInput = parentRow.querySelector('.input-in');
            parentInput.value = levelSums[parentNumber]; // Update the parent row's input
        }
    });


    // Finally, update the top-level total (1)
    updateTopLevelTotal();
}

function updateTopLevelTotal() {
    // Loop through each top-level series row (data-level="1")
    document.querySelectorAll('tr[data-level="1"]').forEach(function (row) {
        let seriesNumber = row.getAttribute('data-number'); // Get the series number (e.g., "1", "2", etc.)
        let subTotal = 0; // Initialize subtotal for the current series

        // Start with the next sibling row
        let nextRow = row.nextElementSibling;

        // Loop through the following rows
        while (nextRow && nextRow.getAttribute('data-number').startsWith(seriesNumber)) {
            let nextRowLevel = nextRow.getAttribute('data-level');

            // Only process rows that are one level deeper, i.e., data-level="2"
            if (nextRowLevel === '2') {
                let value = parseFloat(nextRow.querySelector('.input-in').value) || 0;
                subTotal += value; // Add to the subtotal
            }

            // Move to the next sibling row
            nextRow = nextRow.nextElementSibling;
        }

        // Update the input field for the top-level series row (data-level="1") with the subtotal
        let level1Input = row.querySelector('.input-in');
        level1Input.value = subTotal.toFixed(2); // Set subtotal for the top-level series (format to 2 decimal places)

        calculateLevel1Total();
    });
}

function calculateLevel1Total() {
        // Initialize a variable to store the sum of level 1 rows
        let level1Total = 0;

        // Calculate totals for level 1 rows (top categories)
        document.querySelectorAll('tr[data-level="1"]').forEach(function (row) {
            let sum = 0;
            let inputs = row.querySelectorAll('.input-in');
            inputs.forEach(function (input) {
                sum += parseFloat(input.value) || 0; // Add the input value or 0 if NaN
            });

            // Add the sum of the current level 1 row to the total
            level1Total += sum;

            // Update the input field for the current level 1 row
            let level1Input = row.querySelector('.input-in');
            level1Input.value = sum.toFixed(2); // Format to 2 decimal places
        });

        // Display the total somewhere on the page (e.g., in a specific element)
        document.getElementById('level1-total').innerText = level1Total.toFixed(2);
    }


function calculateValueTotals() {
    // Initialize an object to store sums for each level
    let levelSums = {};

    // Calculate totals for level 3 rows (sub-subcategories)
    document.querySelectorAll('tr[data-level="3"]').forEach(function (row) {
        let sum = 0;
        let inputs = row.querySelectorAll('.input-value');
        inputs.forEach(function (input) {
            sum += parseFloat(input.value) || 0; // Add the input value or 0 if NaN
        });

        // Get the parent row number (level 2)
        let parentRowNumber = row.getAttribute('data-number').split('.').slice(0, 2).join('.');
        levelSums[parentRowNumber] = (levelSums[parentRowNumber] || 0) + sum; // Accumulate sum for level 2
    });

    // Calculate totals for level 2 rows (subcategories)
    document.querySelectorAll('tr[data-level="2"]').forEach(function (row) {
        let sum = (levelSums[row.getAttribute('data-number')] || 0); // Start with accumulated sum from level 3
        let inputs = row.querySelectorAll('.input-value');
        inputs.forEach(function (input) {
            sum += parseFloat(input.value) || 0; // Add the input value or 0 if NaN
        });

        // Get the parent row number (level 1)
        let parentRowNumber = row.getAttribute('data-number').split('.')[0]; // Main category number
        levelSums[parentRowNumber] = (levelSums[parentRowNumber] || 0) + sum; // Accumulate sum for level 1
    });

    // Update the input values in the parent rows
    Object.keys(levelSums).forEach(function (parentNumber) {
        let parentRow = document.querySelector(`tr[data-number="${parentNumber}"]`);
        if (parentRow) {
            let parentInput = parentRow.querySelector('.input-value');
            parentInput.value = levelSums[parentNumber]; // Update the parent row's input
        }
    });

    // Finally, update the top-level total (1)
    updateValueLevelTotal();
}

function updateValueLevelTotal() {
    // Loop through each top-level series row (data-level="1")
    document.querySelectorAll('tr[data-level="1"]').forEach(function (row) {
        let seriesNumber = row.getAttribute('data-number'); // Get the series number (e.g., "1", "2", etc.)
        let subTotal = 0; // Initialize subtotal for the current series

        // Start with the next sibling row
        let nextRow = row.nextElementSibling;

        // Loop through the following rows
        while (nextRow && nextRow.getAttribute('data-number').startsWith(seriesNumber)) {
            let nextRowLevel = nextRow.getAttribute('data-level');

            // Only process rows that are one level deeper, i.e., data-level="2"
            if (nextRowLevel === '2') {
                let value = parseFloat(nextRow.querySelector('.input-value').value) || 0;
                subTotal += value; // Add to the subtotal
            }

            // Move to the next sibling row
            nextRow = nextRow.nextElementSibling;
        }

        // Update the input field for the top-level series row (data-level="1") with the subtotal
        let level1Input = row.querySelector('.input-value');
        level1Input.value = subTotal.toFixed(2); // Set subtotal for the top-level series (format to 2 decimal places)

        calculatevalueLevel1Total();
    });
}

function calculatevalueLevel1Total() {
        // Initialize a variable to store the sum of level 1 rows
        let level1Total = 0;

        // Calculate totals for level 1 rows (top categories)
        document.querySelectorAll('tr[data-level="1"]').forEach(function (row) {
            let sum = 0;
            let inputs = row.querySelectorAll('.input-value');
            inputs.forEach(function (input) {
                sum += parseFloat(input.value) || 0; // Add the input value or 0 if NaN
            });

            // Add the sum of the current level 1 row to the total
            level1Total += sum;

            // Update the input field for the current level 1 row
            let level1Input = row.querySelector('.input-value');
            level1Input.value = sum.toFixed(2); // Format to 2 decimal places
        });

        // Display the total somewhere on the page (e.g., in a specific element)
        document.getElementById('level1-value').innerText = level1Total.toFixed(2);
    }







    // Function to generate row HTML
    function generateRow1(number, level) {
        return `
        <tr class="categorybud" data-level="${level}" data-number="${number}">
            <td>${number}</td>
            <td>
                <div class="d-flex align-items-center">
                    <input type="text" name="Heads[${number}][${level}]" value="" class="form-control" />
                    <a href="#" class="budgetaddcatsub add-btn"><i data-feather="plus-circle"></i></a>
                    <a href="#" class="budgetaddcatsub remove-btn text-danger"><i data-feather="minus-circle"></i></a>
                </div>
            </td>
            <td><input type="text" name="In[${number}][${level}]" readonly value="0" class="form-control mw-100 input-in" /></td>
            <td><input type="text" name="Value[${number}][${level}]" readonly value="0.00" class="form-control mw-100 text-end input-value" /></td>
            <td><input type="text" name="Actual[]" disabled="disabled" class="form-control mw-100 text-end" value="0.00" /></td>
            <td><input type="text" name="Achieve[]" disabled="disabled" class="form-control mw-100" value="0" /></td>
            <td></td>
        </tr>`;
    }

    function generateRow11(number, level,parent) {
        return `
        <tr class="sub-totol sub-categorybud" data-level="${level}" data-number="${number}">
            <td>${number}</td>
            <td>
                <div class="d-flex align-items-center">
                    <input type="text" name="Heads[${parent}][${number}]" value="" class="form-control" />
                    <a href="#" class="budgetaddcatsub add-btn"><i data-feather="plus-circle"></i></a>
                    <a href="#" class="budgetaddcatsub remove-btn text-danger"><i data-feather="minus-circle"></i></a>
                </div>
            </td>
            <td><input type="text" name="In[${parent}][${number}]" readonly value="0" class="form-control mw-100 input-in" /></td>
            <td><input type="text" name="Value[${parent}][${number}]" readonly value="0.00" class="form-control mw-100 text-end input-value" /></td>
            <td><input type="text" name="Actual[]" disabled="disabled" class="form-control mw-100 text-end" value="0.00" /></td>
            <td><input type="text" name="Achieve[]" disabled="disabled" class="form-control mw-100" value="0" /></td>
            <td>
                <button type="button"  class="btn p-25 btn-sm btn-outline-secondary open-modal" style="font-size: 10px">Ledger</button>
            </td>
        </tr>`;
    }

    function generateRow111(number, level,parent) {
        return `
        <tr class="busnesheadbud" data-level="${level}" data-number="${number}">
            <td>&nbsp;</td>
            <td>
                <div class="d-flex align-items-center">
                    <input type="text" name="Heads[${parent}][${number}]" value="" class="form-control" />
                    <a href="#" class="budgetaddcatsub add-btn"><i data-feather="plus-circle"></i></a>
                    <a href="#" class="budgetaddcatsub remove-btn text-danger"><i data-feather="minus-circle"></i></a>
                </div>
            </td>
            <td><input type="text" name="In[${parent}][${number}]" value="0" class="form-control mw-100 input-in" /></td>
            <td><input type="text" name="Value[${parent}][${number}]"  value="0.00" class="form-control mw-100 text-end input-value" /></td>
            <td><input type="text" name="Actual[]" disabled="disabled" class="form-control mw-100 text-end" value="0.00" /></td>
            <td><input type="text" name="Achieve[]" disabled="disabled" class="form-control mw-100" value="0" /></td>
            <td>
                <button type="button" class="btn p-25 btn-sm btn-outline-secondary open-modal" style="font-size: 10px">Ledger</button>
            </td>
        </tr>`;
    }

    // Function to insert rows after the entire series (e.g., after 1.1.1 or 1.2.1)
    function insertAfterAllSeries(currentRow, parentNumber, level, newRowHTML) {
        let rows = document.querySelectorAll('tr');
        let lastInSeries = null;

        rows.forEach(function (row) {
            let number = row.getAttribute('data-number');
            if (number && number.startsWith(parentNumber + '.')) {
                lastInSeries = row;
            }
        });

        if (lastInSeries) {
            lastInSeries.insertAdjacentHTML('afterend', newRowHTML); // Insert the subtree after the last row of the series
        } else {
            currentRow.insertAdjacentHTML('afterend', newRowHTML); // Insert subtree directly after the current row
        }
    }
});

</script>
@endsection

