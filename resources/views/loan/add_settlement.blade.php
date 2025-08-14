@extends('layouts.app')

@section('content')

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
								<h2 class="content-header-title float-start mb-0">New Settlement</h2>
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
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						<div class="form-group breadcrumb-right">   
							<button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>  
							<button class="btn btn-primary btn-sm mb-50 mb-sm-0" form="settle-add-update"><i data-feather="check-circle"></i> Submit</button> 
						</div>
					</div>
				</div>
			</div>
            <div class="content-body">
                 
                
				
				<section id="basic-datatable">
                <form action="{{route('loan.settlement.add-update')}}" method="POST" enctype="multipart/form-data" id="settle-add-update">
                @csrf
                    <div class="row">
                        <div class="col-12">  
							
                            <div class="card">
								 <div class="card-body customernewsection-form"> 
                                 <input type="hidden" name="status_val" value="1">
											
											<div class="border-bottom mb-2 pb-25">
                                                     <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="newheader "> 
                                                                <h4 class="card-title text-theme">Basic Information</h4>
                                                                <p class="card-text">Fill the details</p> 
                                                            </div>
                                                        </div> 

                                                         
                                                    </div>
                                     
                                             </div>  
  
											
											<div class="row"> 
                                                
                                            <!-- <div class="col-md-3">
                                                         <div class="row align-items-center mb-1"> 
                                                            <div class="col-md-12"> 
                                                                <label class="form-label">Book Type <span class="text-danger">*</span></label>  
                                                            </div> 

                                                            <div class="col-md-12">
                                                              <select class="form-select book_typeSelect" name="book_type" required onchange="fetchLoanSeries(this.value, 'settle_series');">
                                                                  <option value="">Select</option>
                                                                  @if(isset($book_type))
                                                                    @foreach($book_type as $key => $val)
                                                                        <option value="{{ $val->id }}">{{ $val->name }}</option>
                                                                    @endforeach
                                                                @endif
                                                              </select>
                                                            </div> 
                                                        </div>
                                                     </div> -->
                                                     <div class="col-md-3">
                                                         <div class="row align-items-center mb-1"> 
                                                            <div class="col-md-12"> 
                                                                <label class="form-label">Series <span class="text-danger">*</span></label>  
                                                            </div> 

                                                            <div class="col-md-12">
                                                              <select class="form-select" name="settle_series" id="settle_series" required>
                                                                <option value="">Select</option>
                                                                @if(isset($book_type))
                                                                    @foreach($book_type as $key => $val)
                                                                        <option value="{{ $val->id }}">{{ $val->book_name }}</option>
                                                                    @endforeach
                                                                @endif
                                                              </select>
                                                            </div> 
                                                        </div>
                                                     </div>
                                                     
                                                     <div class="col-md-3">
                                                         <div class="row align-items-center mb-1"> 
                                                            <div class="col-md-12"> 
                                                                <label class="form-label">Document No. <span class="text-danger">*</span></label>  
                                                            </div> 

                                                            <div class="col-md-12">
                                                              <input type="text" class="form-control" name="settle_document_no" id="settle_document_no" required>
                                                              <span id="settle_document_no_error_message" class="text-danger"></span>
                                                            <span id="settle_document_no_span"></span>
                                                              @error('settle_document_no')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                            </div> 
                                                        </div> 
                                                     </div>
                                                 
                                                
                                     </div>
                                     
                                     <div class="row">
                                                     
                                                     <div class="col-md-3">
                                                         <div class="row align-items-center mb-1"> 
                                                            <div class="col-md-12"> 
                                                                <label class="form-label">Application No. <span class="text-danger">*</span></label>  
                                                            </div> 

                                                            <div class="col-md-12">    
                                                                <select class="form-select" name="settle_application_no" id="settle_application_no" required>
                                                                    <option value="">Select</option>
                                                                    @if(isset($applicants))
                                                                        @foreach($applicants as $key => $val)
                                                                        <option value="{{ $val->id }}" {{ isset($recovery->home_loan_id) && $recovery->home_loan_id == $val->id ? 'selected' : '' }}>{{ $val->appli_no }}</option>
                                                                        @endforeach
                                                                    @endif
                                                              </select>  
                                                            </div>  
                                                        </div>
                                                     
                                                     </div>
                                                     
                                                     
                                                     <div class="col-md-3">
                                                         <div class="row align-items-center mb-1"> 
                                                            <div class="col-md-12"> 
                                                                <label class="form-label">Customer <span class="text-danger">*</span></label>  
                                                            </div> 

                                                            <div class="col-md-12">
                                                              <input type="text" readonly id="settle_customer" name="settle_customer" class="form-control" value="">
                                                              @error('settle_customer')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                            </div> 
                                                        </div>
                                                     
                                                     </div>
                                                   
                                                     <div class="col-md-3">
                                                         <div class="row align-items-center mb-1"> 
                                                            <div class="col-md-12"> 
                                                                <label class="form-label">Loan Type <span class="text-danger">*</span></label>  
                                                            </div> 

                                                            <div class="col-md-12">
                                                                <input type="text" readonly id="settle_loan_type" name="settle_loan_type"  value="" class="form-control">
                                                                @error('settle_loan_type')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                            </div>

                                                        </div> 
                                                     
                                                     </div> 
                                         
                                         </div>
                                     
                                     <div class="row">
                                                 <div class="col-md-3">
                                                         <div class="row align-items-center mb-1"> 
                                                            <div class="col-md-12"> 
                                                                <label class="form-label">Bal. Loan Amount <span class="text-danger">*</span></label>  
                                                            </div> 

                                                            <div class="col-md-12">   
                                                                <input type="number" value="" readonly name="settle_bal_loan_amnnt" id="settle_bal_loan_amnnt" required class="form-control">
                                                                @error('settle_bal_loan_amnnt')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                            </div>

                                                        </div>
                                                     
                                                     </div>
                                         
                                             <div class="col-md-3">
                                                         <div class="row align-items-center mb-1"> 
                                                            <div class="col-md-12"> 
                                                                <label class="form-label">Principal Bal. Amount <span class="text-danger">*</span></label>  
                                                            </div> 

                                                            <div class="col-md-12">   
                                                                <input type="number" value="" name="settle_prin_bal_amnnt" id="settle_prin_bal_amnnt" required readonly class="form-control">
                                                                @error('settle_prin_bal_amnnt')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                            </div>

                                                        </div>
                                                     
                                                     </div>
                                         
                                             <div class="col-md-3">
                                                         <div class="row align-items-center mb-1"> 
                                                            <div class="col-md-12"> 
                                                                <label class="form-label">Interest Bal. Amount <span class="text-danger">*</span></label>  
                                                            </div> 

                                                            <div class="col-md-12">   
                                                                <input type="number" value="" name="settle_intr_bal_amnnt" id="settle_intr_bal_amnnt" required readonly class="form-control">
                                                                @error('settle_intr_bal_amnnt')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                            </div>

                                                        </div>
                                                     
                                                     </div>
                                     
                                     </div>
                                     
                                     <div class="row">  
                                         
                                             
                                                     
                                         
                                                      
                                                     <div class="col-md-3 finalvalue">
                                                         <div class="row align-items-center mb-1"> 
                                                            <div class="col-md-12"> 
                                                                <label class="form-label">Settlement Amount <span class="text-danger">*</span></label>  
                                                            </div> 

                                                            <div class="col-md-12">   
                                                                <input type="number" name="settle_amnnt" id="settle_amnnt" required class="form-control">
                                                            </div>

                                                         </div>
                                                     
                                                     </div>
                                                     
                                                     <div class="col-md-3 finalvalue">
                                                         <div class="row align-items-center mb-1"> 
                                                            <div class="col-md-12"> 
                                                                <label class="form-label">Write off Amt. <span class="text-danger">*</span></label>  
                                                            </div> 

                                                            <div class="col-md-12">   
                                                                <input type="number" value="" name="settle_wo_amnnt" id="settle_wo_amnnt" required readonly class="form-control">
                                                                @error('settle_wo_amnnt')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                            </div>

                                                         </div>
                                                     
                                                     </div> 
                                                     
                                         
                                        
                                         </div>
                                     
                                           
                                     
                                   <div class="row">  
                                                
                                       
                                                     <div class="col-md-8 revisedvalue">
                                                         <div class="newheader d-flex justify-content-between align-items-end mb-1 border-bottom pb-25">
                                                        <div class="header-left">
                                                            <h4 class="card-title text-theme">Settlement Schedule</h4>
                                                            <p class="card-text">Fill the details</p>
                                                        </div>
                                                    </div>
                                                         
                                                         <div class="table-responsive-md mb-1">
                                                         
                                                         
                                                         
                                                         <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border"> 
                                                            <thead>
                                                                 <tr>
                                                                    <th>#</th>
                                                                    <th>Date</th>
                                                                    <th>Amount Type</th>
                                                                    <th>Loan %</th>
                                                                    <th>Amount</th>
                                                                    <th>Action</th>
                                                                  </tr>
                                                                </thead>
                                                                <tbody id="table-body-settle">
                                                                     <tr>
                                                                        <td id="row-number-settle">1</td>
                                                                        <td><input type="date" name="Settlement[schedule_date][]" class="form-control mw-100 past-date"></td>
                                                                        <td>
                                                                            <select class="form-select mw-100" name="Settlement[schedule_amnt_type][]">
                                                                                <option value="">Select</option>
                                                                                <option value="percent">%age</option>
                                                                                <option value="fixed">Fixed</option>
                                                                            </select>
                                                                        </td>
                                                                        <td><input type="number" name="Settlement[schedule_loan_prcnt][]" class="form-control mw-100"></td>
                                                                        <td><input type="number" value="" name="Settlement[schedule_amnt][]" class="form-control mw-100"></td>
                                                                        <td><a href="#" class="add-bank-row-settle" id="add-bank-row-settle" data-class="add-bank-row-settle"><i data-feather="plus-square"></i></a></td>
                                                                      </tr>

                                                                      @if(isset($loanSettlement) && $loanSettlement->loanSettlementSchedule && $loanSettlement->loanSettlementSchedule->count() > 0)
                                                                            @foreach($loanSettlement->loanSettlementSchedule as $key => $val)
                                                                                <tr>
                                                                                    <td>{{$key + 2}}</td>
                                                                                    <td><input type="date" name="Settlement[schedule_date][]" value="{{ $val->schedule_date ?? '' }}" class="form-control mw-100 past-date"></td>
                                                                                    <td>
                                                                                    <select class="form-select mw-100" name="Settlement[schedule_amnt_type][]">
                                                                                        <option value="">Select</option>
                                                                                        <option value="percent" {{ (isset($val->schedule_amnt_type) ? $val->schedule_amnt_type : '') == 'percent' ? 'selected' : '' }}>%age</option>
                                                                                        <option value="fixed" {{ (isset($val->schedule_amnt_type) ? $val->schedule_amnt_type : '') == 'fixed' ? 'selected' : '' }}>Fixed</option>
                                                                                    </select>
                                                                                    </td>
                                                                                    <td><input type="number" name="Settlement[schedule_loan_prcnt][]" value="{{ $val->schedule_loan_prcnt ?? '' }}" class="form-control mw-100"></td>
                                                                                    <td><input type="number" name="Settlement[schedule_amnt][]" value="{{ $val->schedule_amnt ?? '' }}" class="form-control mw-100"></td>
                                                                                    <td><a href="#" class="text-danger delete-item"><i data-feather="trash-2"></i></a></td>
                                                                                </tr>
                                                                            @endforeach
                                                                        @endif
                                                               </tbody>


                                                        </table>
                                                             
                                                        </div>
                                                   </div>
                                         
                                     </div>
                                     
                                     <div class="row">
                                                     
                                                     <div class="col-md-3">
                                                         <div class="row align-items-center mb-1"> 
                                                            <div class="col-md-12"> 
                                                                <label class="form-label">Upload Document</label>  
                                                            </div> 

                                                            <div class="col-md-12">   
                                                                <input type="file" class="form-control" name="settle_docs[]" id="fileInput" onchange="checkFileTypeandSize(event)" multiple />
                                                                <progress id="uploadProgress" value="0" max="100" style="display:none;"></progress>
                                                                <div id="uploadStatus"></div>
                                                                <div id="fileList"></div>
                                                            </div>

                                                        </div>
                                                     
                                                     </div>
                                                     
                                                     <div class="col-md-6">
                                                         <div class="row  mb-1"> 
                                                            <div class="col-md-12"> 
                                                                <label class="form-label">Remarks</label>  
                                                            </div> 

                                                            <div class="col-md-12">   
                                                                <input type="text" name="remarks" class="form-control" />
                                                            </div>

                                                        </div>

                                                     
                                                     </div>
                                                      
                                                    
                                                    
												</div>

                                                 
                                             
 
								
								</div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal to add new record -->
                    </form>
                </section>

            </div>
        </div>
    </div>
    <!-- END: Content-->

<script type="text/javascript">
var getSeriesUrl = "{{url('loan/get-series')}}";
var getvoucherUrl = "{{url('/get_voucher_no')}}".trim();
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{asset('assets/js/loan.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<script>
    let ballance_amnt = '';
	$(document).ready(function(){

        $('#settle_series').on('change', function() {
            var book_id = $(this).val();
            var request = $('#settle_document_no');
            request.val('');
            if (book_id) {
                $.ajax({
                    url: "{{ url('/loan/get-loan-request') }}/" + book_id,
                    type: "GET",
                    dataType: "json",
                    success: function(data) 
                    {
                        if (data.requestno == 1) {
                            request.prop('readonly', false);
                        }else{
                            request.prop('readonly', true);
                            request.val(data.requestno);
                        }
                    }
                });
            }
        });

        $("#settle_amnnt").change(function() {
            let settle_amnnt = parseFloat($(this).val());
            let bal_looan_amnt = parseFloat($("#settle_bal_loan_amnnt").val());
            
            if (isNaN(bal_looan_amnt) || bal_looan_amnt == 0) {
                $(this).val('');
                alert('Please select Bal. Loan Amount First');
                return false;
            }

            if (settle_amnnt >= bal_looan_amnt) {
                $(this).val('');
                alert('Settlement amount should be less than Bal. Loan Amount');
                return false;
            }

            let total_wo = bal_looan_amnt - settle_amnnt;
            $("#settle_wo_amnnt").val(total_wo);
        });

        $('#fileInput').on('change', function() {
        var files = this.files;
        var $fileList = $('#fileList');

        // Loop through selected files
        $.each(files, function(index, file) {
            var fileSize = (file.size / 1024).toFixed(2) + ' KB'; // File size in KB
            var fileName = file.name;
            var fileExtension = fileName.split('.').pop().toUpperCase(); // Get file extension and make it uppercase

            // Create a new image-uplodasection div
            var $fileDiv = $('<div class="image-uplodasection mb-2"></div>');
            var $fileIcon = $('<i data-feather="file" class="fileuploadicon"></i>');
            var $fileName = $('<span class="file-name d-block"></span>').text(fileExtension + ' file').css('font-size', '10px'); // Display extension
            var $fileInfo = $('<span class="file-info d-block"></span>').text(fileSize).css('font-size', '10px'); // Display file size on the next line
            var $deleteDiv = $('<div class="delete-img text-danger"><i data-feather="x"></i></div>');

            $fileDiv.append($fileIcon).append($fileName).append($fileInfo).append($deleteDiv);
            $fileList.append($fileDiv);
            feather.replace();
        });
    });

    // Optional: Handle delete button click to remove the fileDiv
    $(document).on('click', '.delete-img', function() {
        $(this).closest('.image-uplodasection').remove();
    });

        $(document).on('change', '#settle_application_no', function() {
		var customerID = $(this).val();

		$.ajax({
			url: '{{ route("loan.get.customer") }}',
			data: {
				id: customerID
			},
			dataType: 'json',
			success: function(data) {
                let loanData = 'Loan';
                if(data.customer_record.type == 1){
                    loanData = 'Home ' + loanData;
                }else if(data.customer_record.type == 1){
                    loanData = 'Vehicle ' + loanData;
                }else if(data.customer_record.type == 1){
                    loanData = 'Term ' + loanData;
                }
				$("#settle_customer").val(data.customer_record.name);
                $("#settle_loan_type").val(loanData);
                // $("#settle_bal_loan_amnnt").val((parseFloat(data.customer_record.bal_princ_amnt) || 0) + (parseFloat(data.customer_record.rec_intrst) || 0));
                $("#settle_bal_loan_amnnt").val(data.customer_record.recovery_total);
                ballance_amnt = data.balance_amount;
                $("#settle_prin_bal_amnnt").val(data.customer_record.recovery_pa);
                $("#settle_intr_bal_amnnt").val(data.customer_record.recovery_ia);
			},
			error: function(xhr, status, error) {
				console.log('AJAX Error:', status, error);
			}
		});
	});

	$(document).on('input', '.principal-amnt', function() {
        var principalAmount = parseFloat($(this).val());
        var interestRate = parseFloat($('#recovery_interest').val());
        var $row = $(this).closest('tr');
        
        if (!isNaN(principalAmount) && !isNaN(interestRate)) {
            var interestAmount = (principalAmount * interestRate) / 100;
            var totalAmount = principalAmount + interestAmount;

            $row.find('.interest-rate').val(interestAmount);
            $row.find('.total-amount').val(totalAmount);
        } else {
            $row.find('.interest-rate').val('');
            $row.find('.total-amount').val('');
        }
    });

	function getOrdinalSuffix(i) {
        var j = i % 10,
            k = i % 100;
        if (j == 1 && k != 11) {
            return "st";
        }
        if (j == 2 && k != 12) {
            return "nd";
        }
        if (j == 3 && k != 13) {
            return "rd";
        }
        return "th";
    }
		$('tbody').on('click', '#add-bank-row-settle', function(e) {
                e.preventDefault();
				$("#disburs_da").attr('readonly', true);
                var $tbody = $(this).closest('tbody');
                var tbodyId = $tbody.attr('id');
                var clickedClass = $(this).attr('id');
                var $firstTdClass = $(this).closest('tr').find('td:first').attr('id');

                var $currentRow = $(this).closest('tr');
                var $newRow = $currentRow.clone(true, true);

                var isValid = $currentRow.find('input').filter(function() {
                    return $(this).val().trim() !== '';
                }).length > 0;

                if (!isValid) {
                    alert('At least one field must be filled before adding a new row.');
                    return;
                }

                $currentRow.find('input').val('');
                var acHeldValue = $currentRow.find('select[name="Settlement[schedule_amnt_type][]"]').val();

                // Update row number for the new row
                var nextIndex = $('#' + tbodyId + ' tr').length + 1;
                $newRow.find('#' + $firstTdClass).text(nextIndex);
                $newRow.find('#' + clickedClass).removeClass(clickedClass).removeAttr('id').removeAttr('data-class').addClass('text-danger delete-item').html('<i data-feather="trash-2"></i>');
                if(acHeldValue){
                    $newRow.find('select[name="Settlement[schedule_amnt_type][]"]').val(acHeldValue);
                    $currentRow.find('select[name="Settlement[schedule_amnt_type][]"]').val('');
                }
                $('#' + tbodyId).append($newRow);
                feather.replace();
            });

            $('tbody').on('click', '.delete-item', function(e) {
                e.preventDefault();
                
                var $tableBody = $(this).closest('tbody');
                
                $(this).closest('tr').remove();

                var $firstTdId = $(this).closest('tr').find('td:first').attr('id');
                $tableBody.find('tr').each(function(index) {
                    var $rowNumber = $(this).find('#' + $firstTdId);
                    if ($rowNumber.length) {
                        $rowNumber.text(index + 1);
                    }
					if ($rowNumber.length && index > 0) {
						alert('data');
					}
                });
            });

			$("#disbursement_amnt").on('change', function() {
				var selectedValue = $(this).val();
				if (selectedValue === "percent") {
					// Make the input field editable
					$("#dis_mile").removeAttr('readonly');
				} else {
					// Make the input field read-only
					$("#dis_mile").attr('readonly', true);
				}
			});
	});
    var baseUrl = "{{ asset('storage/') }}";
	$(document).on('click', '#assess', function() {
		var loanId = $(this).data('loan-id');
		var loanAmnt = $(this).data('loan-amnt');
		var loanName = $(this).data('loan-name') || '-';
		var loanCreatedAt = $(this).data('loan-created-at') || '-';
		var createData = loanCreatedAt.split(' ')[0];
		$("#ass_para").html(`${loanName} | ${loanAmnt} | ${createData}`);

		// Set the loan ID and amount in the form
		$("#id_loan").val(loanId);
		$("#amnt_loan").val(loanAmnt);

		$.ajax({
			url: '{{ route("get.loan.assess") }}',
			data: {
				id: loanId
			},
			dataType: 'json',
			success: function(data) {
				if (data.assess) {
					$("#ass_recom_amnt").val(data.assess.ass_recom_amnt || '');
					$("#ass_cibil").val(data.assess.ass_cibil || '');
					$("#ass_remarks").val(data.assess.ass_remarks || '');
					if (data.assess.ass_doc) {
						var hiddenInputHtml = '<input type="hidden" name="stored_ass_doc" value="' + data.assess.ass_doc + '" class="form-control" />';
						$("#hidden_inputs").html(hiddenInputHtml);
						var docUrl = "{{ asset('storage') }}" + '/' + data.assess.ass_doc;
						var linkHtml = '<a href="' + docUrl + '" target="_blank">Assessment Doc</a>';
						$("#doc_link").html(linkHtml);
					}
				} else {
					console.log('No assessment data found.');
				}
			},
			error: function(xhr, status, error) {
				console.log('AJAX Error:', status, error);
			}
		});
	});

	$(document).on('click', '#disburs', function() {
		var loanIdd = $(this).data('loan-id');
		var lloanAmnt = $(this).data('loan-amnt');
		var lloanName = $(this).data('loan-name') || '-';
		var lloanCreatedAt = $(this).data('loan-created-at') || '-';
		var ccreateData = lloanCreatedAt.split(' ')[0];
		$("#dis_para").html(`${lloanName} | ${lloanAmnt} | ${ccreateData}`);

		$("#idd_loan").val(loanIdd);
		$("#lloan_amount").val(lloanAmnt);

		$.ajax({
			url: '{{ route("get.loan.disbursemnt") }}',
			data: {
				id: loanIdd
			},
			dataType: 'json',
			success: function(data) {
				try {
					var disbursal_amnt = data.loan_amount.disbursal_amnt;
					console.log(disbursal_amnt);
					$('#disbursement_amnt option').each(function() {
						if ($(this).val() == disbursal_amnt) {
							$(this).prop('selected', true);
						}
					});
					$("#table-body-dis").html(data.disburs);
				} catch (e) {
					console.error('Error inserting HTML:', e);
				}
				feather.replace();
			},
			error: function(xhr, status, error) {
				console.log('AJAX Error:', status, error);
			}
		});
	});

	$(document).on('click', '#docc', function() {
		var loanIdDoc = $(this).data('loan-id');

		$.ajax({
			url: '{{ route("get.loan.docc") }}',
			data: {
				id: loanIdDoc
			},
			dataType: 'json',
			success: function(data) {
				$('#documents-tbody').html(data.doc);
				feather.replace();
			},
			error: function(xhr, status, error) {
				console.log('AJAX Error:', status, error);
			}
		});
	});

	$(document).on('click', '#r_schedule', function() {
		var rloanId = $(this).data('loan-id');
		var rloanAmnt = $(this).data('loan-amnt');
		var rloanName = $(this).data('loan-name') || '-';
		var rloanCreatedAt = $(this).data('loan-created-at') || '-';
		var rcreateData = rloanCreatedAt.split(' ')[0];
		$("#ass_parar").html(`${rloanName} | ${rloanAmnt} | ${rcreateData}`);

		$("#rid_loan").val(rloanId);
		$("#ramnt_loan").val(rloanAmnt);

		$.ajax({
			url: '{{ route("get.loan.recovery.schedule") }}',
			data: {
				id: rloanId
			},
			dataType: 'json',
			success: function(data) {
				$("#repayment-schedule").html('');
				$("#repayment-schedule").html(data.recovery_data);
				$("#recovery_sentioned").val(data.loan_data.recovery_sentioned);
				$("#recovery_repayment_period").val(data.loan_data.recovery_repayment_period);
			},
			error: function(xhr, status, error) {
				console.log('AJAX Error:', status, error);
			}
		});
	});

	function downloadDocumentsZip() {
    var zip = new JSZip();
    var hasDocuments = false;

    // Select all <a> tags inside <tr> > <td> inside #documents-tbody
    var links = document.querySelectorAll('#documents-tbody tr td a');
    
    if (links.length === 0) {
      alert('No documents available to download.');
      return;
    }
    
    var linksProcessed = 0;
    
    links.forEach(function(link, index) {
      if (link.href.length > 0) {
        hasDocuments = true;
        // Fetch the document content
        fetch(link.href)
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.blob();
          })
          .then(blob => {
            // Add to zip file
            var fileName = `document_${index + 1}.${link.href.split('.').pop()}`;
            zip.file(fileName, blob);

            linksProcessed++;
            // Check if all files are added
            if (linksProcessed === links.length) {
              zip.generateAsync({ type: 'blob' })
                .then(function(content) {
                  // Trigger download
                  saveAs(content, 'documents.zip');
                });
            }
          })
          .catch(error => console.error('Error downloading file:', error));
      }
    });

    if (!hasDocuments) {
      alert('No valid documents to download.');
    }
  }

	

	$(window).on('load', function () {
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

		var keyword='';
		if (dt_basic_table.length) {
			var dt_basic = dt_basic_table.DataTable({
				processing: true,
                serverSide: true,
				ajax: {
					url: "{{ route('loan.index') }}",
					data: function (d) {
						d.date = $("#fp-range").val(),
						d.ledger = $("#filter-ledger-name").val(),
						d.status = $("#filter-status").val(),
						d.type = $("#filter-ledger-type").val(),
						d.keyword = keyword
					}
				},
                columns: [
					{ data: null, className: 'dt-center', defaultContent: '<div class="form-check form-check-inline"><input class="form-check-input row-checkbox" type="checkbox"></div>', orderable: false },
					{ data: 'appli_no', name: 'appli_no' },
					{ data: 'ref_no', name: 'ref_no' },
					{ data: 'proceed_date', name: 'proceed_date' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'mobile', name: 'mobile' },
                    { data: 'type', name: 'type' },
					{ data: 'loan_amount', name: 'loan_amount' },
					{ data: 'age', name: 'age' },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
				drawCallback: function() {
					feather.replace();
				},
                dom: 'Bfrtip',
				order: [[0, 'desc']],
				dom:
					'<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
				displayLength: 7,
				lengthMenu: [7, 10, 25, 50, 75, 100],
				buttons: [
					{
						extend: 'collection',
						className: 'btn btn-outline-secondary dropdown-toggle',
						text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + 'Export',
						buttons: [
							{
								extend: 'print',
								text: feather.icons['printer'].toSvg({ class: 'font-small-4 mr-50' }) + 'Print',
								className: 'dropdown-item',
								exportOptions: { columns: [3, 4, 5, 6, 7] }
							},
							{
								extend: 'csv',
								text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'Csv',
								className: 'dropdown-item',
								exportOptions: { columns: [3, 4, 5, 6, 7] }
							},
							{
								extend: 'excel',
								text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
								className: 'dropdown-item',
								exportOptions: { columns: [3, 4, 5, 6, 7] }
							},
							{
								extend: 'pdf',
								text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + 'Pdf',
								className: 'dropdown-item',
								exportOptions: { columns: [3, 4, 5, 6, 7] }
							},
							{
								extend: 'copy',
								text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + 'Copy',
								className: 'dropdown-item',
								exportOptions: { columns: [3, 4, 5, 6, 7] }
							}
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

		// Filter record
		$(".apply-filter").on("click", function () {
			// Redraw the table
			dt_basic.draw();

			// Remove the custom filter function to avoid stacking filters
			// $.fn.dataTable.ext.search.pop();

			// Hide the modal
			$(".modal").modal("hide");
		})

		// Delete Record
		$('.datatables-basic tbody').on('click', '.delete-record', function () {
			dt_basic.row($(this).parents('tr')).remove().draw();
		});
	});

	document.addEventListener('DOMContentLoaded', function() {
    // Select all input fields of type number
    const numberInputs = document.querySelectorAll('input[type="number"]');
    
    // Loop through each input field
    numberInputs.forEach(function(input) {
        // Add an input event listener to each number input
        input.addEventListener('input', function() {
            // If the value is negative, set it to its absolute value
            if (this.value < 0) {
                this.value = Math.abs(this.value);
            }
        });

        // Add a blur event listener to ensure no negative values on losing focus
        input.addEventListener('blur', function() {
            if (this.value < 0) {
                this.value = Math.abs(this.value);
            }
        });
    });
});	

function fetchSeriesBased(series_id, id) {
    $.ajax({
        url: getvoucherUrl + '/' + series_id,
        method: 'GET',
        success: function(response) {
            if (response.type=="Auto") {
                $("#" + id).attr("readonly", true);
                $("#" + id).val(response.voucher_no);
            } else {
                $("#" + id).attr("readonly", false); 
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('An error occurred while fetching the data.');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
        const pastDateInputs = document.querySelectorAll('.past-date');
        const futureDateInputs = document.querySelectorAll('.future-date');
        
        function readonlyates() {
            const today = new Date().toISOString().split('T')[0];

            pastDateInputs.forEach(input => {
                input.setAttribute('max', today);
            });

            futureDateInputs.forEach(input => {
                input.setAttribute('min', today);
            });
        }    
        readonlyates();
    });

    document.addEventListener('DOMContentLoaded', function() {
    const appliNoInput = document.getElementById('settle_document_no');
    const errorMessage = document.getElementById('settle_document_no_error_message'); 
    const appli_span = document.getElementById('settle_document_no_span')

    function validateAppliNo() {
        const value = appliNoInput.value.trim();
        
        // Check if the string starts with a negative sign
        if (value.startsWith('-')) {
            appli_span.textContent = '';
            errorMessage.textContent = 'The Document number must not start with a negative sign.';
            return false;
        }

        // Check if the string contains only allowed characters (letters, numbers, and dashes)
        const regex = /^[a-zA-Z0-9-_]+$/; 
        if (!regex.test(value)) {
            appli_span.textContent = '';
            errorMessage.textContent = 'The Document number can only contain letters, numbers, dashes and underscores.';
            return false;
        }

        // If all checks pass, clear the error message
        errorMessage.textContent = '';
        return true;
    }

    // Validate on blur
    appliNoInput.addEventListener('blur', validateAppliNo);
});

document.addEventListener('DOMContentLoaded', function() {
    const textInputs = document.querySelectorAll('input[type="text"]');
    
    textInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            if (this.value.length > 250) {
                alert('You have exceeded the 250 character limit. Extra characters will be removed.');
                this.value = this.value.substring(0, 250);
            }
        });

        input.addEventListener('blur', function() {
            if (this.value.length > 250) {
                alert('You have exceeded the 250 character limit. Extra characters will be removed.');
                this.value = this.value.substring(0, 250);
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const textInputs = document.querySelectorAll('input[type="number"]');
    
    textInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            if (this.value.length > 11) {
                alert('You have exceeded the 11 character limit. Extra characters will be removed.');
                this.value = this.value.substring(0, 11);
            }
        });

        input.addEventListener('blur', function() {
            if (this.value.length > 11) {
                alert('You have exceeded the 11 character limit. Extra characters will be removed.');
                this.value = this.value.substring(0, 11);
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const textareas = document.querySelectorAll('textarea');
    
    textareas.forEach(function(textarea) {
        function enforceCharacterLimit() {
            if (this.value.length > 500) {
                alert('You have exceeded the 500 character limit. Extra characters will be removed.');
                this.value = this.value.substring(0, 500);
            }
        }

        textarea.addEventListener('input', enforceCharacterLimit);
        textarea.addEventListener('blur', enforceCharacterLimit);
    });
});
</script>

@endsection