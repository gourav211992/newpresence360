@extends('layouts.app')
@section('content')

@php
        $unauthorizedMonths = [];
        foreach ($fy_months as $month) {
            if (!$month['authorized']) {
                $unauthorizedMonths[] = $month['fy_month'];
            }
        }
@endphp

<script>
    const unauthorizedMonths = @json($unauthorizedMonths);
</script>
    <!-- BEGIN: Content-->
     <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-6  mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Depreciation</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{route('/')}}">Home</a></li> 
                                    <li class="breadcrumb-item active"><a href="{{route('finance.fixed-asset.registration.index')}}">Fixed Assets</a></li> 
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="content-header-right text-md-end col-md-6 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">  
                        <a href="{{route('finance.fixed-asset.depreciation.index')}}" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</a>
                        <button type="submit" form="fixed-asset-depreciation-form" class="btn btn-primary btn-sm"
                            id="submit-btn">
                            <i data-feather="check-circle"></i> Submit
                        </button>
                    </div>
                </div>
            </div>
            <div class="content-body"> 
                 <section id="basic-datatable">
                    <div class="row">
                        <form id="fixed-asset-depreciation-form" method="POST"
                        action="{{ route('finance.fixed-asset.depreciation.store') }}"
                        enctype="multipart/form-data"
                        @csrf
                        <input type="hidden" name="asset_details" id="asset_json" value="">
                                 
                        <input type ="hidden" id ="to_date_param">
                        <input type ="hidden" id ="days">
                        <input type ="hidden" name="book_code" id ="book_code_input">
                        <input type="hidden" name="doc_number_type" id="doc_number_type">
                        <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
                        <input type="hidden" name="doc_prefix" id="doc_prefix">
                        <input type="hidden" name="doc_suffix" id="doc_suffix">
                        <input type="hidden" name="doc_no" id="doc_no">
                        <input type="hidden" name="document_status" id="document_status" value="">
                        <input type="hidden" name="grand_total_current_value" id="grand_total_current_value" value="">
                        <input type="hidden" name="grand_total_current_value_after_dep" id="grand_total_current_value_after_dep" value="">
                        <input type="hidden" name="grand_total_dep_amount" id="grand_total_dep_amount" value="" >
                        <input type="hidden" name="grand_total_after_dep_value" id="grand_total_after_dep_value" value="">
                   
                     

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


                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label for="book_id" class="form-label">Series <span class="text-danger">*</span></label>
                                                    </div>
                                            
                                                    <div class="col-md-5">
                                                        <select id="book_id" name="book_id" class="form-select" required>
                                                            @foreach($series as $book)
                                                                <option value="{{ $book->id }}">{{ $book->book_code }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label for="document_number" class="form-label">Document No <span class="text-danger">*</span></label>
                                                    </div>
                                            
                                                    <div class="col-md-5">
                                                        <input type="text" id="document_number" name="document_number" class="form-control" required>
                                                    </div>
                                                </div>
                                            
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label for="document_date" class="form-label">Date <span class="text-danger">*</span></label>
                                                    </div>
                                            
                                                    <div class="col-md-5">
                                                        <input type="date" id="document_date" name="document_date" class="form-control" value="{{date('Y-m-d')}}" required>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select id="location" class="form-select"
                                                            name="location_id" required>
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}">
                                                                    {{ $location->store_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                </div>
                                                <div class="row align-items-center mb-1 cost_center">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Cost Center <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select id="cost_center" class="form-select"
                                                            name="cost_center_id" required>
                                                        </select>
                                                    </div>

                                                </div>

                                            
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label for="period" class="form-label">Period <span class="text-danger">*</span></label>
                                                    </div>
                                            
                                                    <div class="col-md-5">
                                                        <select id="period" name="period" class="form-select" required>
                                                                @php
                                                                $periodCollection = collect($periods);
                                                            @endphp

                                                            @if ($periodCollection->isNotEmpty())
                                                                <option value="{{ $periodCollection->first()->value }}">{{ $periodCollection->first()->label }}</option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                            
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">&nbsp;</label>
                                                    </div>
                                            
                                                    <div class="col-md-5 action-button">
                                                        <a type="button" id="process_btn" class="btn btn-outline-primary btn-sm mb-0">
                                                            <i data-feather="search"></i> Process
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                             
                                            
                                        </div> 
                                </div>
                            </div>
                            
                              
							
                            <div class="card">
								 <div class="card-body customernewsection-form"> 
                                     
                                     
                                            <div class="border-bottom pb-25">
                                                     <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="newheader "> 
                                                                <h4 class="card-title text-theme">Category Wise Detail</h4>
                                                                <p class="card-text">View the details below</p>
                                                            </div>
                                                        </div> 
                                                    </div> 
                                             </div>
											 
											 
											  
  
											
											<div class="row"> 
                                                
                                                 <div class="col-md-12" id="category_wise_detail">
                                                     
                                                     
                                                     <div class="col-md-12 earn-dedtable">
                                                            <table class="table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                               <thead>
                                                                 <tr>
                                                                    <th>#</th>
                                                                    <th>Category</th>
                                                                    <th>Asset Code</th>
                                                                    <th>Sub Asset Code</th>
                                                                    <th>Asset Name</th>
                                                                    <th>Ledger Name</th>
                                                                    <th>FY</th>
                                                                    <th>From Date</th>
                                                                    <th>To Date</th>
                                                                    <th>Posted Days</th>
                                                                    <th>Days</th>
                                                                    <th hidden class="text-end">Current Value</th>
                                                                    <th class="text-end">Return Down Value</th>
                                                                    <th class="text-end">Dep. Amount</th>
                                                                    <th class="text-end">After Dep. Value</th>
                                                                  </tr>
                                                                </thead>
                                                                <tbody id="assetTableBody">
                                                                      
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <td colspan="11" class="text-center">Grand Total</td>
                                                                        <td hidden id="grand_total_current" class="text-end"></td>
                                                                        <td id="grand_total_current_after_dep" class="text-end"></td>
                                                                        <td id="grand_total_dep" class="text-end"></td>
                                                                        <td id="grand_total_after_dep" class="text-end"></td>
                                                                    </tr>
                                                                    
                                                                </tfoot>
                                                              

                                                        </table>
                                                    
                                                      
                                                      
												</div> 
                                                 
                                             </div> 
								</div>
                            </div>
                             
                            
                        </div>
                    </div>
                        </form>
                    <!-- Modal to add new record -->
                     
                </section> 

                 

            </div>
        </div>
    </div>
     <div class="modal fade text-start" id="postvoucher" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Post Voucher</h4>
                        <p class="mb-0">View Details</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					 <div class="row">
                         
                         <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Series <span class="text-danger">*</span></label>
                                <input class="form-control" disabled value="VOUCH/2024" />
                            </div>
                        </div>
                         
                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Voucher No <span class="text-danger">*</span></label>
                                <input class="form-control" disabled value="098" />
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Voucher Date <span class="text-danger">*</span></label>
                                <input class="form-control" disabled value="30/09/2024" />
                            </div>
                        </div>
                         
                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Currency <span class="text-danger">*</span></label>
                                <input class="form-control" disabled value="Indian Rupees" />
                            </div>
                        </div>
                         
                         
						 <div class="col-md-12">
 

							<div class="table-responsive">
								<table class="mt-1 table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
									<thead>
										 <tr>
											<th>Type</th>  
											<th>Leadger Group</th>
											<th>Leadger Code</th>
											<th>Leadger Name</th>
                                            <th class="text-end">Debit</th>
                                            <th class="text-end">Credit</th>
											<th>Remarks</th>
										  </tr>
										</thead>
										<tbody>
											 <tr>
												<td>Asset</td>   
												<td class="fw-bolder text-dark">Fixed Asset</td> 
                                                <td>LAP001</td>
                                                <td>Laptop</td>
                                                <td class="text-end">-</td>
												<td class="text-end">4,000.00</td>
												<td>Remarks come here...</td>
											</tr>
											
											<tr>
												<td>Asset</td>   
												<td class="fw-bolder text-dark">Fixed Asset</td> 
                                                <td>TAB001</td>
                                                <td>Tables</td>
                                                <td class="text-end">-</td>
												<td class="text-end">5,000.00</td>
												<td>Remarks come here...</td>
											</tr>
                                            
                                            <tr>
												<td>Depreciation</td>   
												<td class="fw-bolder text-dark">Indirect Expense</td> 
                                                <td>Dep001</td>
                                                <td>Depreciation</td>
                                                <td class="text-end">9.000.00</td>
												<td class="text-end">-</td>
												<td>Remarks come here...</td>
											</tr>
                                            
                                            <tr>
												<td colspan="4" class="fw-bolder text-dark text-end">Total</td>   
												<td class="fw-bolder text-dark text-end">9.000.00</td> 
												<td class="fw-bolder text-dark text-end">9,000.00</td>
											</tr>
											
											
											 
											 

									   </tbody>


								</table>
							</div>
						</div>


					 </div>
				</div>
				<div class="modal-footer text-end">
					<button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
					<button class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i data-feather="check-circle"></i> Submit</button>
				</div>
			</div>
		</div>
	</div>
    
    @section('scripts')
    <script>
        document.getElementById('document_date').addEventListener('input', function() {
            if (!isDateAuthorized(this.value)) {
                this.value = '';
                this.focus();
            }
        });

        function getMonthName(ym) {
            // ym = '2024-07'
            const [year, month] = ym.split('-');
            const d = new Date(year, parseInt(month) - 1);
            return d.toLocaleString('default', {
                month: 'long',
                year: 'numeric'
            });
        }

        function isDateAuthorized(dateValue) {
        if (!dateValue) return true; // allow empty, you can tweak this logic if needed
        var selectedMonth = dateValue.substring(0, 7);
        if (unauthorizedMonths.includes(selectedMonth)) {
            var monthLabel = getMonthName(selectedMonth);

            Swal.fire({
                icon: 'error',
                title: 'Unauthorized Month',
                text: 'You are not authorized to select dates from ' + monthLabel +
                    '. Please select another month.',
                confirmButtonText: 'OK'
            });

            return false;
        }
        return true;
    }


    </script>
        <script>
            function isDateInRange(dateToCheck, startDate, endDate) {
    // Convert all dates to Date objects
    var check = new Date(dateToCheck);
    var start = new Date(startDate);
    var end = new Date(endDate);

    // Normalize time (optional, ensures dates are compared by day only)
    check.setHours(0,0,0,0);
    start.setHours(0,0,0,0);
    end.setHours(0,0,0,0);

    // Check if date is in range
    return check >= start && check <= end;
}

    $('#period').on('change', function () {
        let selectedRange = $(this).val(); // "01-04-2025 to 31-03-2026"

        if (selectedRange) {
            let parts = selectedRange.split(" to ");
            let startDate = new Date(parts[0].split('-').reverse().join('-')); // Convert to "YYYY-MM-DD"
            let endDate = new Date(parts[1].split('-').reverse().join('-'));   // Same format
            //console.log(endDate);

            let today = new Date();

            // Clear time portion for accurate comparison
            today.setHours(0, 0, 0, 0);
            startDate.setHours(0, 0, 0, 0);
            endDate.setHours(0, 0, 0, 0);

            let resultDate;

            if (today < startDate || today > endDate) {
                resultDate = endDate;
                $('#submit-btn').show();
            } else {
                resultDate = today;
                $('#submit-btn').hide();

            }



            // Format the result date as DD-MM-YYYY
            let resultDates = new Date(resultDate); // or your date
            let day = String(resultDates.getDate()).padStart(2, '0');
            let month = String(resultDates.getMonth() + 1).padStart(2, '0'); // Months are zero-based
            let year = resultDates.getFullYear();

            let formatted = `${year}-${month}-${day}`;

            // Use this date as needed (e.g., set to an input field)
            $('#to_date_param').val(formatted);
            let timeDifferenceInMs = endDate - startDate;
            let totalDays = Math.floor(timeDifferenceInMs / (1000 * 60 * 60 * 24)) + 1;
            $('#days').val(totalDays);
        }
    });
     $('#period').trigger('change');


    function showToast(icon, title) {
    const capitalizedIcon = icon.charAt(0).toUpperCase() + icon.slice(1);
    Swal.fire({
        title: capitalizedIcon,
        text: title,
        icon: icon, // Keep the original icon lowercase for Swal
    });
}

$('#fixed-asset-depreciation-form').on('submit', function(e) {
                $('.preloader').show();
                e.preventDefault(); 
                this.submit();
            });

            // const Toast = Swal.mixin({
            //     toast: true,
            //     position: "top-end",
            //     showConfirmButton: false,
            //     timer: 3000,
            //     timerProgressBar: true,
            //     didOpen: (toast) => {
            //         toast.onmouseenter = Swal.stopTimer;
            //         toast.onmouseleave = Swal.resumeTimer;
            //     },
            // });
            // Toast.fire({
            //     icon,
            //     title
            // });
        

        @if (session('success'))
         $('.preloader').hide();
            showToast("success", "{{ session('success') }}");
        @endif

        @if (session('error'))
         $('.preloader').hide();
            showToast("error", "{{ session('error') }}");
        @endif

        @if ($errors->any())
         $('.preloader').hide();
            showToast('error',
                "@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach"
            );
        @endif
       $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })
document.getElementById("process_btn").addEventListener("click", function () {
    let period = document.getElementById("period").value;

    if (!period) {
        $('#assetTableBody').empty();
         document.getElementById("grand_total_current_value_after_dep").value =  0;
            document.getElementById("grand_total_current_value").value =  0;
            document.getElementById("grand_total_dep_amount").value = 0;
            document.getElementById("grand_total_after_dep_value").value = 0;
            document.getElementById("grand_total_current").textContent = 0;
            document.getElementById("grand_total_current_after_dep").textContent = 0;
            document.getElementById("grand_total_dep").textContent = 0;
            document.getElementById("grand_total_after_dep").textContent = 0;
            document.getElementById("asset_json").value = "";
        showToast('error',"Please select period.");
        return;
    }


    // Example: Fetch data from the backend using AJAX (replace with actual data source)
    fetch(`{{route('finance.fixed-asset.depreciation.assets')}}?date_range=${period}`)
        .then(response => response.json())
        .then(data => {
            let tableBody = document.getElementById("assetTableBody");
            tableBody.innerHTML = ""; // Clear previous data

            let totalCurrentValue = 0, totalDepAmount = 0, totalAfterDepValue = 0,totalAfterDepCurrent=0;
            let sno =1;
            let assetDataArray = [];

             if (data && data.length > 0) {      

            data.forEach((asset, index) => {
                let expire = false;
                if (asset.sub_asset && asset.sub_asset.length > 0) {
                asset.sub_asset.forEach((sub_asset, index) => {
                function parseYMD(dateStr) {
                    return new Date(`${dateStr}`); // Convert to ISO format
                }
                function formatDate(DateObj){
                        let d = DateObj.getDate().toString().padStart(2, '0');
                        let m = (DateObj.getMonth()+1).toString().padStart(2, '0');
                        let y = DateObj.getFullYear();
                        return `${d}-${m}-${y}`;
                }

                let fromDateObj = parseYMD(sub_asset.last_dep_date);
                let toDateObj = parseYMD($('#to_date_param').val());
                let fromDateObjCap = parseYMD(sub_asset.capitalize_date);
                let expiryDate = parseYMD(sub_asset.expiry_date); // Months are 0-indexed
                let to_date = formatDate(toDateObj);
                let from_date = formatDate(fromDateObj);
                
                if (expiryDate <= toDateObj) {
                        toDateObj = expiryDate;
                        to_date = formatDate(expiryDate);
                        expire = true;
                  }
                let diffTime = toDateObj - fromDateObj;
                let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))+1;

              

              
                if(diffDays>0){
                    let depType = asset.depreciation_method;
                    //console.log("dep_method"+depType);
                    let fy = @json($fy);

                        // Determine which asset value to use based on $dep_type
                        let value;
                        //console.log()
                        if (depType === "SLM") {
                            value = sub_asset.current_value;
                            //console.log("selected_method SLM");
                        } else {
                            let isCurrent = isDateInRange(sub_asset.capitalize_date,"{{$financialStartDate}}","{{$financialEndDate}}");
                            if(isCurrent)
                                value = sub_asset.current_value;
                            else
                            value = sub_asset.current_value_after_dep;
                        
                        } 
                    //console.log("DepRate:"+asset.depreciation_percentage);
                    //console.log("DiffDays:"+diffDays);

                    let totalDepreciation = ((parseFloat(asset.depreciation_percentage/100)*parseFloat(value)) * diffDays / 365);
                    
                    // if (asset?.category?.setup?.act_type === "income_tax") {
                    //     //console.log("Income Tax Depreciation"+sub_asset.sub_asset_code);
                    //     const capitalizeDate = new Date(sub_asset.capitalize_date);
                    //     const cutoffDate = new Date(capitalizeDate.getFullYear(), 9, 3); // October is month 9 (0-indexed)
                    //     totalDepreciation = ((parseFloat(asset.depreciation_percentage/100)*parseFloat(value)));
                    //     if (capitalizeDate>cutoffDate) {
                    //         totalDepreciation = totalDepreciation/2;
                    //         //console.log("half year");
                    //     } else {
                    //         //console.log("full year");
                    //     } 
                    //  }
                    
                    let after_dep_value = parseFloat(sub_asset.current_value_after_dep) - totalDepreciation;
                    let salv = parseFloat(sub_asset.salvage_value);
                    let diff = parseFloat(after_dep_value) - salv;
                    
                    if(expire && (diff>0.0) && (depType === "WDV"))
                    {
                        totalDepreciation = parseFloat(totalDepreciation) + parseFloat(diff);  
                        after_dep_value = parseFloat(sub_asset.current_value_after_dep) - totalDepreciation;
                    }
                    
                    
                    let posted_days = 0;

                        if (asset.dep_type !== null && asset.dep_type !== "{{$dep_type}}") {
                            // Calculate difference between capitalize_date and last_dep_date
                            let capitalizeDate = new Date(sub_asset.capitalize_date);
                            let lastDepDate = new Date(sub_asset.last_dep_date);

                            // Make sure both dates are valid
                            if (!isNaN(capitalizeDate) && !isNaN(lastDepDate)) {
                                let timeDiff = lastDepDate - capitalizeDate; // In milliseconds
                                posted_days = Math.floor(timeDiff / (1000 * 60 * 60 * 24)); // Convert to days
                            } 
                        }
                   

                    let assetData = {
                        asset_id: asset.id,
                        category: asset.category.name,
                        asset_code: asset.asset_code,
                        sub_asset_code: sub_asset.sub_asset_code,
                        sub_asset_id: sub_asset.id,
                        asset_name: asset.asset_name,
                        ledger_name: asset.ledger.name,
                        fy: fy,
                        from_date: from_date,
                        to_date: to_date,
                        posted_days: posted_days,
                        days: diffDays,
                        current_value: sub_asset.current_value,
                        current_value_after_dep: sub_asset.current_value_after_dep,
                        dep_amount: totalDepreciation,
                        after_dep_value: after_dep_value
                    };

                    assetDataArray.push(assetData);
                
                let row = `<tr>                                
                            <td>${sno}</td>
                                <td class="text-dark fw-bolder">
                                    <input type="hidden" name="assets[]" value="${asset.id}">
                                    <input type="hidden" name="sub_assets[]" value="${sub_asset.id}">
                                    <input type="hidden" name="category[]" value="${asset.category.name}">
                                    ${asset.category.name}
                                </td>
                                <td>
                                    <input type="hidden" name="asset_code[]" value="${asset.asset_code}">
                                    ${asset.asset_code}
                                </td>
                                 <td>
                                    <input type="hidden" name="sub_asset_code[]" value="${sub_asset.sub_asset_code}">
                                    ${sub_asset.sub_asset_code}
                                </td>
                                 <td>
                                    <input type="hidden" name="asset_name[]" value="${asset.asset_name}">
                                    ${asset.asset_name}
                                </td>
                                <td>
                                    <input type="hidden" name="ledger_name[]" value="${asset.ledger_name}">
                                    ${asset.ledger.name}
                                </td>
                                <td>
                                    <input type="hidden" name="fy[]" value="{{$fy}}">
                                    {{$fy}}
                                </td>
                                 <td>
                                    <input type="hidden" name="from_date[]" value="${from_date}">
                                    ${from_date}
                                </td>
                                 <td>
                                    <input type="hidden" name="to_date[]" value="${to_date}">
                                    ${to_date}
                                </td>
                                 <td>
                                    <input type="hidden" name="posted_days[]" value="${posted_days}">
                                    ${posted_days}
                                </td>
                                <td>
                                    <input type="hidden" name="days[]" value="${diffDays}">
                                    ${diffDays}
                                </td>
                                <td hidden class="text-end">
                                    <input type="hidden" name="current_value[]" 
                                    value="${sub_asset.current_value}">
                                    ${formatIndianNumber(sub_asset.current_value)}                                
                                </td>
                                 <td class="text-end">
                                    <input type="hidden" name="current_value_after_dep[]" 
                                    value="${sub_asset.current_value_after_dep}">
                                    ${formatIndianNumber(sub_asset.current_value_after_dep)}                                
                                </td>
                                <td class="text-end">
                                    <input type="hidden" name="dep_amount[]" value="${totalDepreciation}">
                                    ${formatIndianNumber(totalDepreciation)}
                                </td>
                                <td class="text-end">
                                    <input type="hidden" name="after_dep_value[]" value="${after_dep_value}">
                                    ${formatIndianNumber(after_dep_value)}
                                </td>
                            </tr>`;
                            sno++;

                            tableBody.insertAdjacentHTML("beforeend", row);
                

                // Sum up totals
                totalAfterDepCurrent+=parseFloat(sub_asset.current_value_after_dep)
                totalCurrentValue += parseFloat(sub_asset.current_value);
                totalDepAmount += parseFloat(totalDepreciation);
                totalAfterDepValue += parseFloat(after_dep_value);

            }else{
            // console.log("DIFFF"+diffDays,toDateObj,fromDateObj);
            // console.log("COde"+asset.asset_code);
            }
                });
            }
            });
        }

            // Update Grand Total
            document.getElementById("grand_total_current_value_after_dep").value =  totalAfterDepCurrent;
            document.getElementById("grand_total_current_value").value =  totalCurrentValue;
            document.getElementById("grand_total_dep_amount").value = totalDepAmount;
            document.getElementById("grand_total_after_dep_value").value = totalAfterDepValue;
            document.getElementById("grand_total_current").textContent = formatIndianNumber(totalCurrentValue);
            document.getElementById("grand_total_current_after_dep").textContent = formatIndianNumber(totalAfterDepCurrent);
            document.getElementById("grand_total_dep").textContent = formatIndianNumber(totalDepAmount);
            document.getElementById("grand_total_after_dep").textContent = formatIndianNumber(totalAfterDepValue);
            document.getElementById("asset_json").value = JSON.stringify(assetDataArray);
            
        })
        .catch(error => console.error("Error fetching data:", error));
});
        function resetParametersDependentElements(data) {
            let backDateAllowed = false;
            let futureDateAllowed = false;

            if (data != null) {
                //console.log(data.parameters.back_date_allowed);
                if (Array.isArray(data?.parameters?.back_date_allowed)) {
                    for (let i = 0; i < data.parameters.back_date_allowed.length; i++) {
                        if (data.parameters.back_date_allowed[i].trim().toLowerCase() === "yes") {
                            backDateAllowed = true;
                            break; // Exit the loop once we find "yes"
                        }
                    }
                }
                if (Array.isArray(data?.parameters?.future_date_allowed)) {
                    for (let i = 0; i < data.parameters.future_date_allowed.length; i++) {
                        if (data.parameters.future_date_allowed[i].trim().toLowerCase() === "yes") {
                            futureDateAllowed = true;
                            break; // Exit the loop once we find "yes"
                        }
                    }
                }
                ////console.log(backDateAllowed, futureDateAllowed);

            }

            const dateInput = document.getElementById("document_date");

            // Determine the max and min values for the date input
            const today = moment().format("YYYY-MM-DD");

            if (backDateAllowed && futureDateAllowed) {
                dateInput.setAttribute("min","{{$financialStartDate}}");
                dateInput.setAttribute("max","{{$financialEndDate}}");
            } else if (backDateAllowed) {
                dateInput.setAttribute("max", today);
                dateInput.setAttribute("min","{{$financialStartDate}}");
            } else if (futureDateAllowed) {
                dateInput.setAttribute("min", today);
                dateInput.setAttribute("max","{{$financialEndDate}}");
            } else {
                dateInput.setAttribute("min", today);
                dateInput.setAttribute("max", today);
            }
        }

        $('#book_id').on('change', function() {
            resetParametersDependentElements(null);
            let currentDate = new Date().toISOString().split('T')[0];
            let document_date = $('#document_date').val();
            let bookId = $('#book_id').val();
            let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId +
                "&document_date=" + document_date;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        resetParametersDependentElements(data.data);
                        $("#book_code_input").val(data.data.book_code);
                        if (!data.data.doc.document_number) {
                            $("#document_number").val('');
                            $('#doc_number_type').val('');
                            $('#doc_reset_pattern').val('');
                            $('#doc_prefix').val('');
                            $('#doc_suffix').val('');
                            $('#doc_no').val('');
                        } else {
                            $("#document_number").val(data.data.doc.document_number);
                            $('#doc_number_type').val(data.data.doc.type);
                            $('#doc_reset_pattern').val(data.data.doc.reset_pattern);
                            $('#doc_prefix').val(data.data.doc.prefix);
                            $('#doc_suffix').val(data.data.doc.suffix);
                            $('#doc_no').val(data.data.doc.doc_no);
                        }
                        if (data.data.doc.type == 'Manually') {
                            $("#document_number").attr('readonly', false);
                        } else {
                            $("#document_number").attr('readonly', true);
                        }

                    }
                    if (data.status == 404) {
                        $("#document_number").val('');
                        $('#doc_number_type').val('');
                        $('#doc_reset_pattern').val('');
                        $('#doc_prefix').val('');
                        $('#doc_suffix').val('');
                        $('#doc_no').val('');
                        alert(data.message);
                    }
                });
            });
        });
        $('#book_id').trigger('change');

        document.getElementById('submit-btn').addEventListener('click', function() {
            document.getElementById('document_status').value = 'submitted';
        });

    $('#location').on('change', function () {
    var locationId = $(this).val();

    if (locationId) {
        // Build the route manually
        var url = '{{ route("cost-center.get-cost-center", ":id") }}'.replace(':id', locationId);

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if(data.length==0){
                    $('#cost_center').empty(); 
                $('#cost_center').prop('required', false);
                $('.cost_center').hide();
                }
                else{
                    $('.cost_center').show();
                    $('#cost_center').prop('required', true);
                $('#cost_center').empty(); // Clear previous options
                $.each(data, function (key, value) {
                    $('#cost_center').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
            }
            },
            error: function () {
                $('#cost_center').empty();
            }
        });
    } else {
        $('#cost_center').empty();
    }
});



$('#location').trigger('change');

    </script>
@endsection
<!-- END: Body-->

@endsection